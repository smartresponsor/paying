<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function paying_shell_arg(string $value): string
{
    return escapeshellarg($value);
}

function paying_run(string $label, array $commandParts): array
{
    $command = implode(' ', array_map('paying_shell_arg', $commandParts));
    $output = [];
    $exitCode = 0;

    exec($command . ' 2>&1', $output, $exitCode);

    return [
        'label' => $label,
        'exitCode' => $exitCode,
        'output' => $output,
    ];
}

function paying_phpunit_parts(string $root, array $arguments): array
{
    $parts = [PHP_BINARY];
    $phpWrapper = paying_path($root, 'tools/php/php84.php');

    if (is_file($phpWrapper)) {
        $parts[] = $phpWrapper;
    }

    return array_merge($parts, [
        paying_path($root, 'vendor/bin/phpunit'),
        '--configuration',
        paying_path($root, 'phpunit.xml.dist'),
    ], $arguments);
}

function paying_php_report_parts(string $root, string $script): array
{
    $parts = [PHP_BINARY];
    $phpWrapper = paying_path($root, 'tools/php/php84.php');

    if (is_file($phpWrapper)) {
        $parts[] = $phpWrapper;
    }

    $parts[] = paying_path($root, $script);

    return $parts;
}

function paying_has_issue_marker(array $output): bool
{
    foreach ($output as $line) {
        if (
            str_contains($line, 'OK, but there were issues!')
            || str_contains($line, 'Warnings:')
            || str_contains($line, 'PHPUnit Warnings:')
            || str_contains($line, 'Deprecations:')
            || str_contains($line, 'PHPUnit Deprecations:')
            || str_contains($line, 'Notices:')
            || str_contains($line, 'PHPUnit Notices:')
            || str_contains($line, 'Skipped:')
        ) {
            return true;
        }
    }

    return false;
}

$jobs = [
    ['label' => 'Composer validate', 'command' => ['composer', 'validate'], 'strictZero' => true],
    ['label' => 'RC-2 canonical readiness', 'command' => paying_php_report_parts($root, 'tools/inspection/PayingCanonicalReadinessReport.php'), 'strictZero' => true],
    ['label' => 'Runtime proof closure', 'command' => paying_php_report_parts($root, 'tools/inspection/PayingRuntimeProofClosureReport.php'), 'strictZero' => true],
    ['label' => 'RC-3 milestone', 'command' => paying_php_report_parts($root, 'tools/inspection/PayingRc3MilestoneReport.php'), 'strictZero' => true],
    ['label' => 'RC-3 handoff', 'command' => paying_php_report_parts($root, 'tools/inspection/PayingRc3HandoffReport.php'), 'strictZero' => true],
    ['label' => 'RC-3 final closure', 'command' => paying_php_report_parts($root, 'tools/inspection/PayingRc3FinalClosureReport.php'), 'strictZero' => true],
    ['label' => 'RC-3 transfer memo', 'command' => paying_php_report_parts($root, 'tools/inspection/PayingRc3TransferMemoReport.php'), 'strictZero' => true],
    ['label' => 'Unit tests', 'command' => paying_phpunit_parts($root, ['--testsuite', 'unit']), 'strictZero' => true],
    ['label' => 'Functional tests', 'command' => paying_phpunit_parts($root, ['--testsuite', 'functional']), 'strictZero' => true],
    ['label' => 'Security tests', 'command' => paying_phpunit_parts($root, ['--filter', 'PaymentScopeGuardSubscriberTest']), 'strictZero' => true],
    ['label' => 'Runtime issue inventory', 'command' => paying_php_report_parts($root, 'tools/inspection/PayingRuntimeIssueInventoryReport.php'), 'strictZero' => false],
];

$results = [];

echo "Paying runtime proof core report\n";
echo "================================\n";
echo 'Project root: ' . str_replace('\\', '/', $root) . "\n";
echo 'Jobs scheduled: ' . count($jobs) . "\n\n";

foreach ($jobs as $job) {
    $result = paying_run($job['label'], $job['command']);
    $hasIssues = paying_has_issue_marker($result['output']);
    $status = $result['exitCode'] === 0 ? ($hasIssues ? 'OK_WITH_ISSUES' : 'OK') : 'FAILED';

    $results[] = $result + [
        'status' => $status,
        'hasIssues' => $hasIssues,
        'strictZero' => $job['strictZero'],
    ];

    echo '[' . $status . '] ' . $job['label'] . "\n";
    echo '  Exit code: ' . $result['exitCode'] . "\n";

    foreach (array_slice($result['output'], -10) as $line) {
        echo '  ' . $line . "\n";
    }

    echo "\n";
}

$blockingFailures = array_values(array_filter(
    $results,
    static fn (array $result): bool => $result['strictZero'] && $result['exitCode'] !== 0
));

$issueCarriers = array_values(array_filter(
    $results,
    static fn (array $result): bool => $result['exitCode'] === 0 && $result['hasIssues']
));

echo 'Blocking failures: ' . count($blockingFailures) . "\n";
foreach ($blockingFailures as $failure) {
    echo '- ' . $failure['label'] . "\n";
}

echo 'Green jobs with issues: ' . count($issueCarriers) . "\n";
foreach ($issueCarriers as $issueCarrier) {
    echo '- ' . $issueCarrier['label'] . "\n";
}

if ($blockingFailures !== []) {
    echo "Status: FAILED\n";
    exit(1);
}

if ($issueCarriers !== []) {
    echo "Status: OK_WITH_ISSUES\n";
    exit(0);
}

echo "Status: OK\n";
