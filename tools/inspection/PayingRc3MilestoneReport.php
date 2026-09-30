<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

$errors = [];
$composerPath = paying_path($root, 'composer.json');
$composer = [];

if (!is_file($composerPath)) {
    $errors[] = 'composer.json was not found.';
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

$requiredScripts = [
    'report:paying-canonical-readiness',
    'report:runtime-issue-inventory',
    'report:runtime-proof-closure',
    'report:runtime-proof-core',
    'report:composer-script-hygiene',
    'report:rc3-milestone',
    'report:rc3-handoff',
    'test:unit',
    'test:functional',
    'test:security',
];

$requiredFiles = [
    'tools/inspection/PayingCanonicalReadinessReport.php',
    'tools/inspection/PayingRuntimeIssueInventoryReport.php',
    'tools/inspection/PayingRuntimeProofClosureReport.php',
    'tools/inspection/PayingRuntimeProofCoreReport.php',
    'tools/inspection/PayingComposerScriptHygieneReport.php',
    'tools/inspection/PayingRc3MilestoneReport.php',
    'tools/inspection/PayingRc3HandoffReport.php',
    'docs/architecture/paying-rc2-canonical-readiness.md',
    'docs/architecture/paying-wave43-runtime-proof-core.md',
    'docs/architecture/paying-wave45-runtime-proof-closure.md',
    'docs/architecture/paying-wave47-runtime-core-closure-integration.md',
    'docs/architecture/paying-wave48-rc3-milestone-marker.md',
    'docs/architecture/paying-wave49-rc3-milestone-registry.md',
    'docs/architecture/paying-wave50-rc3-milestone-closure-integration.md',
    'docs/architecture/paying-wave51-rc3-milestone-hygiene-closure.md',
    'docs/architecture/paying-wave52-rc3-handoff-memo.md',
    'delivery/rc/paying-rc3-handoff.md',
];

foreach ($requiredFiles as $file) {
    if (!is_file(paying_path($root, $file))) {
        $errors[] = 'Missing RC-3 milestone file: ' . $file;
    }
}

$scripts = isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];

foreach ($requiredScripts as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        $errors[] = 'Missing RC-3 milestone composer script: ' . $scriptName;
    }
}

$hygieneReport = paying_path($root, 'tools/inspection/PayingComposerScriptHygieneReport.php');
if (is_file($hygieneReport)) {
    $hygieneContents = (string) file_get_contents($hygieneReport);
    foreach (['report:rc3-milestone', 'report:rc3-handoff'] as $scriptName) {
        if (!str_contains($hygieneContents, "'" . $scriptName . "'") && !str_contains($hygieneContents, '"' . $scriptName . '"')) {
            $errors[] = 'Composer script hygiene report does not require ' . $scriptName . '.';
        }
    }
} else {
    $errors[] = 'Composer script hygiene report was not found.';
}

$closureReport = paying_path($root, 'tools/inspection/PayingRuntimeProofClosureReport.php');
if (is_file($closureReport)) {
    $closureContents = (string) file_get_contents($closureReport);
    foreach (['report:rc3-milestone', 'report:rc3-handoff'] as $scriptName) {
        if (!str_contains($closureContents, "'" . $scriptName . "'") && !str_contains($closureContents, '"' . $scriptName . '"')) {
            $errors[] = 'Runtime proof closure report does not require ' . $scriptName . '.';
        }
    }
    if (!str_contains($closureContents, 'PayingRc3MilestoneReport.php')) {
        $errors[] = 'Runtime proof closure report does not require PayingRc3MilestoneReport.php.';
    }
    if (!str_contains($closureContents, 'PayingRc3HandoffReport.php')) {
        $errors[] = 'Runtime proof closure report does not require PayingRc3HandoffReport.php.';
    }
} else {
    $errors[] = 'Runtime proof closure report was not found.';
}

$descriptionStatus = isset($composer['description']) && is_string($composer['description']) && trim($composer['description']) !== '' ? 'present' : 'missing';
if ($descriptionStatus === 'missing') {
    $errors[] = 'composer.json description is missing.';
}

$autoloadStatus = isset($composer['autoload']['psr-4']) && is_array($composer['autoload']['psr-4']) && array_key_exists('App\\Paying\\', $composer['autoload']['psr-4']) ? 'App\\Paying\\' : 'missing';
if ($autoloadStatus === 'missing') {
    $errors[] = 'composer.json is missing App\\Paying\\ PSR-4 autoload.';
}

echo "Paying RC-3 milestone marker report\n";
echo "===================================\n";
echo "Component: Paying\n";
echo 'Namespace: ' . $autoloadStatus . "\n";
echo 'Composer description: ' . $descriptionStatus . "\n";
echo 'Required scripts: ' . count($requiredScripts) . "\n";
echo 'Required files: ' . count($requiredFiles) . "\n";
echo 'Errors: ' . count($errors) . "\n";

if ($errors !== []) {
    echo "Status: FAILED\n";
    foreach ($errors as $error) {
        echo '- ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
echo "Milestone: RC-3 runtime proof surface is assembled, handoff-ready, and registry-covered.\n";
