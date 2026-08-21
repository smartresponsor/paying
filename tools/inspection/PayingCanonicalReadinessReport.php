<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function paying_command_line(string $root, string $script): array
{
    $phpWrapper = paying_path($root, 'tools/php/php84.php');

    if (is_file($phpWrapper)) {
        return [PHP_BINARY, $phpWrapper, paying_path($root, $script)];
    }

    return [PHP_BINARY, paying_path($root, $script)];
}

function paying_shell_arg(string $value): string
{
    return escapeshellarg($value);
}

function paying_run_report(string $root, string $label, string $script): array
{
    $scriptPath = paying_path($root, $script);

    if (!is_file($scriptPath)) {
        return [
            'label' => $label,
            'script' => $script,
            'exitCode' => 127,
            'output' => ['Missing report script file: ' . $script],
        ];
    }

    $command = implode(' ', array_map('paying_shell_arg', paying_command_line($root, $script)));
    $output = [];
    $exitCode = 0;

    exec($command . ' 2>&1', $output, $exitCode);

    return [
        'label' => $label,
        'script' => $script,
        'exitCode' => $exitCode,
        'output' => $output,
    ];
}

$reports = [
    [
        'label' => 'Packaging/root surface',
        'script' => 'tools/inspection/PayingPackagingRootSurfaceReport.php',
    ],
    [
        'label' => 'Release-candidate structure',
        'script' => 'tools/inspection/PayingReleaseCandidateStructureReport.php',
    ],
    [
        'label' => 'Canonical structure closure',
        'script' => 'tools/inspection/PayingCanonicalStructureClosureReport.php',
    ],
    [
        'label' => 'Canonical nameEntity-form summary',
        'script' => 'tools/inspection/PayingCanonicalNameFormSummaryReport.php',
    ],
    [
        'label' => 'Application surface nameEntity-form',
        'script' => 'tools/inspection/PayingApplicationSurfaceNameFormReport.php',
    ],
    [
        'label' => 'Source residual nameEntity-form',
        'script' => 'tools/inspection/PayingSourceResidualNameFormReport.php',
    ],
    [
        'label' => 'Entity-first persistence',
        'script' => 'tools/inspection/PayingEntityFirstPersistenceReport.php',
    ],
    [
        'label' => 'Entity-first consistency',
        'script' => 'tools/inspection/PayingEntityFirstConsistencyReport.php',
    ],
    [
        'label' => 'Test canonical closure',
        'script' => 'tools/inspection/PayingTestCanonicalClosureReport.php',
    ],
    [
        'label' => 'Inspection script registry',
        'script' => 'tools/inspection/PayingInspectionScriptRegistryReport.php',
    ],
    [
        'label' => 'Composer script hygiene',
        'script' => 'tools/inspection/PayingComposerScriptHygieneReport.php',
    ],
];

$failed = [];

echo "Paying RC-2 canonical readiness report\n";
echo "======================================\n";
echo 'Project root: ' . str_replace('\\', '/', $root) . "\n";
echo 'Reports scheduled: ' . count($reports) . "\n\n";

foreach ($reports as $report) {
    $result = paying_run_report($root, $report['label'], $report['script']);

    $status = $result['exitCode'] === 0 ? 'OK' : 'FAILED';
    echo '[' . $status . '] ' . $report['label'] . ' — ' . $report['script'] . "\n";

    if ($result['exitCode'] !== 0) {
        $failed[] = $result;

        $excerpt = array_slice($result['output'], -12);
        foreach ($excerpt as $line) {
            echo '    ' . $line . "\n";
        }
    }
}

echo "\n";
echo 'Failed reports: ' . count($failed) . "\n";

if ($failed !== []) {
    echo "Status: FAILED\n";
    echo "Failing report labels:\n";
    foreach ($failed as $failure) {
        echo '- ' . $failure['label'] . ' (' . $failure['script'] . ')' . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
echo "RC-2 canonical readiness gate is green.\n";
