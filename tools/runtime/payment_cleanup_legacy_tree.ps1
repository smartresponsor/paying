$ErrorActionPreference = "Stop"

$ProjectDir = Split-Path -Parent (Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path))
$SourceRoot = Join-Path $ProjectDir "src"
$HoldingRoot = Join-Path $ProjectDir "var/legacy-disabled-src"

function Normalize-RelativePath
{
    param([Parameter(Mandatory = $true)][string]$Path)

    $relative = $Path.Substring($ProjectDir.Length)
    return $relative.TrimStart([char[]]@('\', '/'))
}

function Move-ToQuarantine
{
    param(
        [Parameter(Mandatory = $true)][string]$Path,
        [Parameter(Mandatory = $true)][string]$Reason
    )

    $relative = Normalize-RelativePath -Path $Path
    $target = Join-Path $HoldingRoot $relative
    $targetDir = Split-Path -Parent $target
    if (-not(Test-Path $targetDir))
    {
        New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    }

    if (Test-Path $target)
    {
        $target = "$target.__" + [guid]::NewGuid().ToString("N")
    }

    Move-Item -LiteralPath $Path -Destination $target -Force
    Write-Host ("Quarantined {0} -> {1} [{2}]" -f $relative, (Normalize-RelativePath -Path $target), $Reason)
}

function Get-DeclaredSymbol
{
    param([string]$Path)

    if ([System.IO.Path]::GetExtension($Path).ToLowerInvariant() -ne ".php")
    {
        return $null
    }

    $content = Get-Content -LiteralPath $Path -Raw
    $namespace = ""
    $namespaceMatch = [regex]::Match($content, "namespace\s+([^;{]+)")
    if ($namespaceMatch.Success)
    {
        $namespace = $namespaceMatch.Groups[1].Value.Trim()
    }

    $symbolMatch = [regex]::Match($content, "(?m)^\s*(?:final\s+|abstract\s+)?(class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)")
    if (-not$symbolMatch.Success)
    {
        return $null
    }

    $name = $symbolMatch.Groups[2].Value.Trim()
    if ( [string]::IsNullOrWhiteSpace($namespace))
    {
        return $name
    }

    return "$namespace\\$name"
}

function Get-PathRank
{
    param([string]$Path)
    $normalized = $Path.Replace('\\', '/')
    $score = 0
    if ($normalized -like '*/src/Entity/*')
    {
        $score -= 50
    }
    if ($normalized -match '/[^/]+\.php/')
    {
        $score += 100
    }
    $depth = ($normalized.Trim('/') -split '/').Count
    return @($score, $depth, $normalized.Length, $normalized)
}

if (Test-Path $SourceRoot)
{
    Get-ChildItem -LiteralPath $SourceRoot -Directory -Recurse |
            Sort-Object { $_.FullName.Length } -Descending |
            ForEach-Object {
                $relativePath = Normalize-RelativePath -Path $_.FullName
                foreach ($segment in ($relativePath -split '[\\/]'))
                {
                    if ( $segment.EndsWith('.php', [System.StringComparison]::OrdinalIgnoreCase))
                    {
                        Move-ToQuarantine -Path $_.FullName -Reason 'legacy-php-path-directory'
                        break
                    }
                }
            }

    $map = @{ }
    Get-ChildItem -LiteralPath $SourceRoot -File -Recurse -Filter *.php | ForEach-Object {
        $symbol = Get-DeclaredSymbol -Path $_.FullName
        if ($null -eq $symbol)
        {
            return
        }
        $key = $symbol.ToLowerInvariant()
        if (-not $map.ContainsKey($key))
        {
            $map[$key] = New-Object System.Collections.Generic.List[string]
        }
        $map[$key].Add($_.FullName)
    }

    foreach ($key in $map.Keys)
    {
        if ($map[$key].Count -lt 2)
        {
            continue
        }
        $ordered = $map[$key] | Sort-Object {
            $rank = Get-PathRank -Path $_
            "{0:D6}|{1:D6}|{2:D6}|{3}" -f $rank[0], $rank[1], $rank[2], $rank[3]
        }
        $preferred = $ordered[0]
        $ordered | Select-Object -Skip 1 | ForEach-Object {
            Move-ToQuarantine -Path $_ -Reason ("duplicate-symbol:{0} preferred={1}" -f $key, (Normalize-RelativePath -Path $preferred))
        }
    }
}
