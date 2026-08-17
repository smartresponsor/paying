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

function paying_phpunit_base_parts(string $root): array
{
    $phpWrapper = paying_path($root, 'tools/php/php84.php');
    $phpunit = paying_path($root, 'vendor/bin/phpunit');
    $configuration = paying_path($root, 'phpunit.xml.dist');

    $parts = [PHP_BINARY];

    if (is_file($phpWrapper)) {
        $parts[] = $phpWrapper;
    }

    $parts[] = $phpunit;
    $parts[] = '--configuration';
    $parts[] = $configuration;

    return $parts;
}

function paying_command(string $root, array $arguments): string
{
    $parts = array_merge(paying_phpunit_base_parts($root), $arguments, [
        '--display-warnings',
        '--display-notices',
        '--display-deprecations',
        '--display-skipped',
        '--no-progress',
    ]);

    return implode(' ', array_map('paying_shell_arg', $parts));
}

function paying_run_suite(string $root, string $label, array $arguments): array
{
    $output = [];
    $exitCode = 0;
    exec(paying_command($root, $arguments) . ' 2>&1', $output, $exitCode);

    return [
        'suite' => $label,
        'exitCode' => $exitCode,
        'output' => $output,
    ];
}

function paying_extract_count(array $lines, string $pattern): ?int
{
    foreach ($lines as $line) {
        if (preg_match($pattern, $line, $matches) === 1) {
            return (int) $matches[1];
        }
    }

    return null;
}

function paying_extract_issue_lines(array $lines): array
{
    $issues = [];

    foreach ($lines as $index => $line) {
        if (
            preg_match('/^\d+\)\s+/', $line) === 1
            || str_contains($line, 'Warning:')
            || str_contains($line, 'Notice:')
            || str_contains($line, 'Deprecation:')
            || str_contains($line, 'Skipped:')
            || str_contains($line, 'cannot be found')
            || str_contains($line, 'No tests found')
            || str_contains($line, 'OK, but there were issues!')
            || str_contains($line, 'FAILURES!')
            || str_contains($line, 'ERRORS!')
        ) {
            $issues[] = [
                'line' => $index + 1,
                'text' => $line,
            ];
        }
    }

    return $issues;
}

$suites = [
    [
        'label' => 'unit',
        'arguments' => ['--testsuite', 'unit'],
    ],
    [
        'label' => 'functional',
        'arguments' => ['--testsuite', 'functional'],
    ],
    [
        'label' => 'security',
        'arguments' => ['--filter', 'PaymentScopeGuardSubscriberTest'],
    ],
];

$results = [];

echo "Paying runtime issue inventory report\n";
echo "=====================================\n";

foreach ($suites as $suite) {
    $result = paying_run_suite($root, $suite['label'], $suite['arguments']);
    $lines = $result['output'];

    $warnings = paying_extract_count($lines, '/Warnings:\s+(\d+)/');
    $phpunitWarnings = paying_extract_count($lines, '/PHPUnit Warnings:\s+(\d+)/');
    $deprecations = paying_extract_count($lines, '/Deprecations:\s+(\d+)/');
    $phpunitDeprecations = paying_extract_count($lines, '/PHPUnit Deprecations:\s+(\d+)/');
    $notices = paying_extract_count($lines, '/Notices:\s+(\d+)/');
    $phpunitNotices = paying_extract_count($lines, '/PHPUnit Notices:\s+(\d+)/');
    $skipped = paying_extract_count($lines, '/Skipped:\s+(\d+)/');
    $issues = paying_extract_issue_lines($lines);

    $results[] = [
        'suite' => $suite['label'],
        'exitCode' => $result['exitCode'],
        'warnings' => $warnings,
        'phpunitWarnings' => $phpunitWarnings,
        'deprecations' => $deprecations,
        'phpunitDeprecations' => $phpunitDeprecations,
        'notices' => $notices,
        'phpunitNotices' => $phpunitNotices,
        'skipped' => $skipped,
        'issues' => $issues,
        'tail' => array_slice($lines, -24),
    ];
}

foreach ($results as $result) {
    echo "\n";
    echo strtoupper($result['suite']) . "\n";
    echo str_repeat('-', strlen($result['suite'])) . "\n";
    echo 'Exit code: ' . $result['exitCode'] . "\n";
    echo 'Warnings: ' . ($result['warnings'] ?? 0) . "\n";
    echo 'PHPUnit warnings: ' . ($result['phpunitWarnings'] ?? 0) . "\n";
    echo 'Deprecations: ' . ($result['deprecations'] ?? 0) . "\n";
    echo 'PHPUnit deprecations: ' . ($result['phpunitDeprecations'] ?? 0) . "\n";
    echo 'Notices: ' . ($result['notices'] ?? 0) . "\n";
    echo 'PHPUnit notices: ' . ($result['phpunitNotices'] ?? 0) . "\n";
    echo 'Skipped: ' . ($result['skipped'] ?? 0) . "\n";

    if ($result['issues'] !== []) {
        echo "Issue lines:\n";
        foreach (array_slice($result['issues'], 0, 60) as $issue) {
            echo '  L' . $issue['line'] . ': ' . $issue['text'] . "\n";
        }

        if (count($result['issues']) > 60) {
            echo '  ... ' . (count($result['issues']) - 60) . " more issue lines omitted\n";
        }
    } else {
        echo "Issue lines: none detected\n";
    }

    echo "Tail:\n";
    foreach ($result['tail'] as $line) {
        echo '  ' . $line . "\n";
    }
}

$failed = array_values(array_filter(
    $results,
    static fn (array $result): bool => $result['exitCode'] !== 0
));

echo "\n";
echo 'Suites with non-zero exit code: ' . count($failed) . "\n";

if ($failed !== []) {
    echo "Status: FAILED\n";
    foreach ($failed as $result) {
        echo '- ' . $result['suite'] . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
