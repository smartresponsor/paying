# Marketing America Corp. Oleksandr Tishchenko

$ErrorActionPreference = 'Stop'
$Root = Resolve-Path (Join-Path $PSScriptRoot '..\..')
Set-Location $Root

$Missing = $false
$Files = @(
'composer.json',
'.env.example',
'.env.test',
'phpunit.xml.dist',
'docs/architecture/payment-installed-runtime-proof-track.md'
)

foreach ($File in $Files)
{
    if (Test-Path $File)
    {
        Write-Output "OK   $File"
    }
    else
    {
        Write-Error "MISS $File"
        $Missing = $true
    }
}

if (Test-Path 'composer.lock')
{
    Write-Output 'OK   composer.lock'
}
else
{
    Write-Warning 'composer.lock not committed yet'
}

if (-not(Test-Path 'var'))
{
    New-Item -ItemType Directory -Path 'var' | Out-Null
}
Write-Output 'OK   var'

if ($Missing)
{
    exit 1
}
