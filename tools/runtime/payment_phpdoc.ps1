$ErrorActionPreference = 'Stop'

$Root = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$PharPath = Join-Path $Root 'tools\runtime\phpDocumentor.phar'
$ConfigPath = Join-Path $Root 'phpdoc.dist.xml'

if (-not(Test-Path $PharPath))
{
    Write-Error "Missing $PharPath. Download phpDocumentor PHAR outside the project dependency graph before running docs generation."
}

php $PharPath -c $ConfigPath @args
