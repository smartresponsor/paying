<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function paying_file_contains(string $root, string $relative, string $needle): bool
{
    $path = paying_path($root, $relative);

    return is_file($path) && str_contains((string) file_get_contents($path), $needle);
}

$errors = [];

$requiredFiles = [
    'tools/inspection/PayingCanonicalReadinessReport.php',
    'tools/inspection/PayingComposerScriptHygieneReport.php',
    'tools/inspection/PayingRuntimeIssueInventoryReport.php',
    'tools/inspection/PayingRuntimeProofClosureReport.php',
    'tools/inspection/PayingRuntimeProofCoreReport.php',
    'tools/inspection/PayingRc3MilestoneReport.php',
    'tools/inspection/PayingRc3HandoffReport.php',
    'docs/architecture/paying-wave52-rc3-handoff-memo.md',
    'docs/architecture/paying-wave53-rc3-handoff-registry-integration.md',
    'docs/architecture/paying-wave54-handoff-runtime-core-integration.md',
    'delivery/rc/paying-rc3-handoff.md',
];

foreach ($requiredFiles as $file) {
    if (!is_file(paying_path($root, $file))) {
        $errors[] = 'Missing RC-3 final closure file: ' . $file;
    }
}

$composerPath = paying_path($root, 'composer.json');
$composer = [];

if (!is_file($composerPath)) {
    $errors[] = 'Missing composer.json.';
} else {
    $contents = (string) file_get_contents($composerPath);

    if (str_starts_with($contents, "\xEF\xBB\xBF")) {
        $errors[] = 'composer.json contains UTF-8 BOM.';
    }

    try {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (is_array($decoded)) {
            $composer = $decoded;
        }
    } catch (Throwable $throwable) {
        $errors[] = 'composer.json is not valid JSON: ' . $throwable->getMessage();
    }
}

$scripts = isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];
$requiredScripts = [
    'report:paying-canonical-readiness',
    'report:composer-script-hygiene',
    'report:runtime-issue-inventory',
    'report:runtime-proof-closure',
    'report:runtime-proof-core',
    'report:rc3-milestone',
    'report:rc3-handoff',
    'test:unit',
    'test:functional',
    'test:security',
];

foreach ($requiredScripts as $script) {
    if (!array_key_exists($script, $scripts)) {
        $errors[] = 'Missing RC-3 final closure composer script: ' . $script;
    }
}

$crossChecks = [
    ['tools/inspection/PayingComposerScriptHygieneReport.php', 'report:rc3-handoff', 'Hygiene report must require rc3-handoff.'],
    ['tools/inspection/PayingComposerScriptHygieneReport.php', 'report:rc3-milestone', 'Hygiene report must require rc3-milestone.'],
    ['tools/inspection/PayingRuntimeProofClosureReport.php', 'PayingRc3HandoffReport.php', 'Closure report must require handoff report.'],
    ['tools/inspection/PayingRuntimeProofClosureReport.php', 'PayingRc3MilestoneReport.php', 'Closure report must require milestone report.'],
    ['tools/inspection/PayingRc3MilestoneReport.php', 'report:rc3-handoff', 'Milestone report must require handoff script.'],
    ['tools/inspection/PayingRuntimeProofCoreReport.php', 'PayingRc3HandoffReport.php', 'Runtime proof core must run handoff report.'],
    ['tools/inspection/PayingRuntimeProofCoreReport.php', 'PayingRc3MilestoneReport.php', 'Runtime proof core must run milestone report.'],
];

foreach ($crossChecks as [$file, $needle, $message]) {
    if (!paying_file_contains($root, $file, $needle)) {
        $errors[] = $message . ' Missing needle: ' . $needle . ' in ' . $file;
    }
}

if (!isset($composer['description']) || !is_string($composer['description']) || trim($composer['description']) === '') {
    $errors[] = 'composer.json description is missing.';
}

if (!isset($composer['autoload']['psr-4']) || !is_array($composer['autoload']['psr-4']) || !array_key_exists('App\\Paying\\', $composer['autoload']['psr-4'])) {
    $errors[] = 'composer.json is missing App\\Paying\\ PSR-4 autoload.';
}

echo "Paying RC-3 final closure surface report\n";
echo "========================================\n";
echo "Component: Paying\n";
echo "Namespace: App\\\\Paying\\\\\n";
echo 'Required files: ' . count($requiredFiles) . "\n";
echo 'Required scripts: ' . count($requiredScripts) . "\n";
echo 'Cross-checks: ' . count($crossChecks) . "\n";
echo 'Errors: ' . count($errors) . "\n";

if ($errors !== []) {
    echo "Status: FAILED\n";
    foreach ($errors as $error) {
        echo '- ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
echo "Milestone: RC-3 final closure surface is assembled.\n";
