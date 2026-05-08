<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

$errors = [];
$warnings = [];

$requiredFiles = [
    'composer.json',
    'phpunit.xml.dist',
    'tools/php/php84.php',
    'tools/inspection/PayingCanonicalReadinessReport.php',
    'tools/inspection/PayingRuntimeIssueInventoryReport.php',
    'tools/inspection/PayingRuntimeProofCoreReport.php',
    'tools/inspection/PayingComposerScriptHygieneReport.php',
    'docs/architecture/paying-rc2-canonical-readiness.md',
    'docs/architecture/paying-wave39-runtime-issue-inventory.md',
    'docs/architecture/paying-wave43-runtime-proof-core.md',
    'docs/architecture/paying-wave44-runtime-proof-registry.md',
];

foreach ($requiredFiles as $file) {
    if (!is_file(paying_path($root, $file))) {
        $errors[] = 'Missing required runtime proof closure file: ' . $file;
    }
}

$composerPath = paying_path($root, 'composer.json');
$composer = null;
if (is_file($composerPath)) {
    $contents = (string) file_get_contents($composerPath);

    if (str_starts_with($contents, "\xEF\xBB\xBF")) {
        $errors[] = 'composer.json contains UTF-8 BOM.';
    }

    try {
        $composer = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        $errors[] = 'composer.json is not valid JSON: ' . $exception->getMessage();
    }
}

$requiredScripts = [
    'report:paying-canonical-readiness',
    'report:composer-script-hygiene',
    'report:runtime-issue-inventory',
    'report:runtime-proof-core',
    'test:unit',
    'test:functional',
    'test:security',
];

if (is_array($composer)) {
    if (!isset($composer['description']) || !is_string($composer['description']) || trim($composer['description']) === '') {
        $errors[] = 'composer.json is missing non-empty description.';
    }

    $scripts = isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];

    foreach ($requiredScripts as $scriptName) {
        if (!array_key_exists($scriptName, $scripts)) {
            $errors[] = 'Missing required runtime proof composer script: ' . $scriptName;
        }
    }

    if (isset($scripts['test:security']) && is_string($scripts['test:security']) && !str_contains($scripts['test:security'], 'PaymentScopeGuardSubscriberTest')) {
        $warnings[] = 'test:security does not reference PaymentScopeGuardSubscriberTest filter.';
    }

    if (isset($scripts['report:runtime-proof-core']) && is_string($scripts['report:runtime-proof-core']) && !str_contains($scripts['report:runtime-proof-core'], 'PayingRuntimeProofCoreReport.php')) {
        $errors[] = 'report:runtime-proof-core does not point to PayingRuntimeProofCoreReport.php.';
    }

    if (isset($scripts['report:runtime-issue-inventory']) && is_string($scripts['report:runtime-issue-inventory']) && !str_contains($scripts['report:runtime-issue-inventory'], 'PayingRuntimeIssueInventoryReport.php')) {
        $errors[] = 'report:runtime-issue-inventory does not point to PayingRuntimeIssueInventoryReport.php.';
    }
}

echo "Paying runtime proof closure report\n";
echo "===================================\n";
echo 'Required files: ' . count($requiredFiles) . "\n";
echo 'Required composer scripts: ' . count($requiredScripts) . "\n";
echo 'Warnings: ' . count($warnings) . "\n";
echo 'Errors: ' . count($errors) . "\n";

foreach ($warnings as $warning) {
    echo '[WARN] ' . $warning . "\n";
}

if ($errors !== []) {
    echo "Status: FAILED\n";
    foreach ($errors as $error) {
        echo '- ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
