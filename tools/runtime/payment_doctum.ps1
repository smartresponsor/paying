$ErrorActionPreference = 'Stop'

$Root = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$PharPath = Join-Path $Root 'tools\runtime\doctum.phar'
$ConfigPath = Join-Path $Root 'doctum.php'

if (-not(Test-Path $PharPath))
{
    Write-Error "Missing $PharPath. Download Doctum PHAR outside the project dependency graph before running code reference generation."
}

php $PharPath update $ConfigPath --force @args
