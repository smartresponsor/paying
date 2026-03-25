# Marketing America Corp. Oleksandr Tishchenko
param(
    [switch]$IncludeSmokes,
    [switch]$IncludeReports,
    [switch]$FailOnErrors
)

$ErrorActionPreference = 'Stop'
$Root = [string](Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
Set-Location $Root

$timestamp = [string](Get-Date -Format 'yyyyMMdd-HHmmss')
$reportBase = Join-Path $Root 'var/report/local'
$reportRoot = Join-Path $reportBase $timestamp
$latestRoot = Join-Path $reportBase 'latest'
$logRoot = Join-Path $reportRoot 'logs'
New-Item -ItemType Directory -Force -Path $logRoot | Out-Null
if (Test-Path $latestRoot) { Remove-Item -Recurse -Force $latestRoot }
New-Item -ItemType Directory -Force -Path $latestRoot | Out-Null

$steps = New-Object System.Collections.Generic.List[object]
foreach ($item in @(
    @{ Name = 'install-preflight'; Command = 'composer install:preflight' },
    @{ Name = 'lint'; Command = 'composer lint' },
    @{ Name = 'lint-yaml'; Command = 'composer lint:yaml' },
    @{ Name = 'lint-container'; Command = 'composer lint:container' },
    @{ Name = 'cs-check'; Command = 'composer cs:check-quiet' },
    @{ Name = 'stan'; Command = 'composer stan:runtime-target' },
    @{ Name = 'docs-phpdoc-check'; Command = 'composer docs:phpdoc:check' },
    @{ Name = 'test-bootstrap-reset'; Command = 'composer test:bootstrap:reset' },
    @{ Name = 'test'; Command = 'composer test' }
)) { $steps.Add([pscustomobject]$item) }
if ($IncludeSmokes) {
    foreach ($item in @(
        @{ Name = 'smoke-runtime'; Command = 'composer smoke:runtime' },
        @{ Name = 'smoke-fixtures'; Command = 'composer smoke:fixtures' },
        @{ Name = 'smoke-container'; Command = 'composer smoke:container' },
        @{ Name = 'smoke-doctrine'; Command = 'composer smoke:doctrine' }
    )) { $steps.Add([pscustomobject]$item) }
}
if ($IncludeReports) {
    foreach ($item in @(
        @{ Name = 'report-route-inventory'; Command = 'composer report:route-inventory' },
        @{ Name = 'report-runtime-proof'; Command = 'composer report:runtime-proof' }
    )) { $steps.Add([pscustomobject]$item) }
}

$results = New-Object System.Collections.Generic.List[object]
$pipelineStart = Get-Date
$overallSuccess = $true

foreach ($step in $steps) {
    $safeName = $step.Name -replace '[^a-zA-Z0-9._-]', '-'
    $logFile = Join-Path $logRoot ($safeName + '.log')
    $cmdFile = Join-Path $logRoot ($safeName + '.cmd.txt')
    Set-Content -Path $cmdFile -Value ([string]$step.Command) -Encoding UTF8
    Write-Host ('[RUN ] ' + $step.Name + ' -> ' + $step.Command)
    $stepStart = Get-Date
    $exitCode = 0
    try {
        cmd.exe /d /c $step.Command 2>&1 | Tee-Object -FilePath $logFile
        $exitCode = $LASTEXITCODE
        if ($null -eq $exitCode) { $exitCode = 0 }
    } catch {
        $_ | Out-String | Tee-Object -FilePath $logFile -Append | Out-Null
        $exitCode = 1
    }
    $durationMs = [int]((Get-Date) - $stepStart).TotalMilliseconds
    $status = if ($exitCode -eq 0) { 'passed' } else { 'failed' }
    $results.Add([pscustomobject]@{
        name = [string]$step.Name
        command = [string]$step.Command
        status = [string]$status
        exit_code = [int]$exitCode
        duration_ms = [int]$durationMs
        log = [string]('logs/' + $safeName + '.log')
        command_file = [string]('logs/' + $safeName + '.cmd.txt')
    })
    if ($exitCode -eq 0) {
        Write-Host ('[PASS] ' + $step.Name)
    } else {
        Write-Host ('[FAIL] ' + $step.Name + ' (exit ' + $exitCode + ')')
        $overallSuccess = $false
        if ($FailOnErrors) { break }
    }
}

$durationTotalMs = [int]((Get-Date) - $pipelineStart).TotalMilliseconds
$summary = New-Object System.Collections.Generic.List[string]
$summary.Add('# Payment local pipeline report')
$summary.Add('')
$summary.Add('- Timestamp: ' + $timestamp)
$summary.Add('- Report root: `' + $reportRoot + '`')
$summary.Add('- Result: ' + ($(if ($overallSuccess) { 'PASSED' } elseif ($FailOnErrors) { 'FAILED' } else { 'PASSED_WITH_ISSUES' })))
$summary.Add('- Duration ms: ' + $durationTotalMs)
$summary.Add('- Include smokes: ' + $IncludeSmokes.IsPresent)
$summary.Add('- Include reports: ' + $IncludeReports.IsPresent)
$summary.Add('- Fail on errors: ' + $FailOnErrors.IsPresent)
$summary.Add('')
$summary.Add('| Step | Status | Exit | Duration ms | Log |')
$summary.Add('|---|---:|---:|---:|---|')
foreach ($result in $results) {
    $summary.Add('| ' + $result.name + ' | ' + $result.status + ' | ' + $result.exit_code + ' | ' + $result.duration_ms + ' | `' + $result.log + '` |')
}
$summaryPath = Join-Path $reportRoot 'summary.md'
Set-Content -Path $summaryPath -Value $summary -Encoding UTF8

$pipelineStatus = if ($overallSuccess) { 'passed' } elseif ($FailOnErrors) { 'failed' } else { 'passed_with_issues' }
$reportObject = [pscustomobject]@{
    pipeline = 'payment-local'
    timestamp = [string]$timestamp
    report_root = [string]$reportRoot
    status = [string]$pipelineStatus
    duration_ms = [int]$durationTotalMs
    include_smokes = [bool]$IncludeSmokes.IsPresent
    include_reports = [bool]$IncludeReports.IsPresent
    fail_on_errors = [bool]$FailOnErrors.IsPresent
    steps = @($results.ToArray())
}
$reportJsonPath = Join-Path $reportRoot 'report.json'
$reportObject | ConvertTo-Json -Depth 6 | Set-Content -Path $reportJsonPath -Encoding UTF8
Copy-Item -Path (Join-Path $reportRoot '*') -Destination $latestRoot -Recurse -Force
Set-Content -Path (Join-Path $latestRoot 'LATEST.txt') -Value $timestamp -Encoding UTF8
Set-Content -Path (Join-Path $reportBase 'latest.txt') -Value $timestamp -Encoding UTF8
Write-Host ''
Write-Host ('Report root: ' + $reportRoot)
Write-Host ('Summary: ' + $summaryPath)
Write-Host ('JSON: ' + $reportJsonPath)
if ($FailOnErrors -and -not $overallSuccess) { exit 1 }
exit 0
