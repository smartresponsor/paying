<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function paying_collect_files(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile()) {
            continue;
        }

        $extension = strtolower($item->getExtension());
        if (!in_array($extension, ['php', 'yaml', 'yml', 'xml', 'twig'], true)) {
            continue;
        }

        $files[] = $item->getPathname();
    }

    sort($files);

    return $files;
}

function paying_relative(string $root, string $absolute): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $normalizedAbsolute = str_replace('\\', '/', $absolute);

    if (str_starts_with($normalizedAbsolute, $normalizedRoot)) {
        return substr($normalizedAbsolute, strlen($normalizedRoot));
    }

    return $normalizedAbsolute;
}

$errors = [];
$warnings = [];

$requiredAnchors = [
    'config/services.yaml',
    'src/Controller',
    'src/Entity',
    'src/Repository',
    'src/Service',
    'tools/inspection',
];

foreach ($requiredAnchors as $anchor) {
    if (!file_exists(paying_path($root, $anchor))) {
        $errors[] = 'Missing release-candidate anchor: ' . $anchor;
    }
}

$requiredReports = [
    'tools/inspection/PayingPackagingRootSurfaceReport.php',
    'tools/inspection/PayingCanonicalStructureClosureReport.php',
    'tools/inspection/PayingTestCanonicalClosureReport.php',
    'tools/inspection/PayingCanonicalNameFormSummaryReport.php',
];

foreach ($requiredReports as $report) {
    if (!is_file(paying_path($root, $report))) {
        $errors[] = 'Missing required inspection report: ' . $report;
    }
}

$composerFile = paying_path($root, 'composer.json');
$composer = is_file($composerFile) ? json_decode((string) file_get_contents($composerFile), true) : null;
$scripts = is_array($composer) && isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];

$requiredScripts = [
    'report:packaging-root-surface',
    'report:canonical-structure-closure',
    'report:test-canonical-closure',
    'report:canonical-name-form',
];

foreach ($requiredScripts as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        $errors[] = 'Missing required composer script: ' . $scriptName;
    }
}

/*
 * Double-prefix detection is intentionally scoped to runtime/application surfaces.
 * Inspection reports contain literal guard strings such as PaymentPayment* by design,
 * so tools/inspection must not self-trigger the release-candidate report.
 */
$scanRoots = [
    'src',
    'tests',
    'config',
];

foreach ($scanRoots as $scanRoot) {
    foreach (paying_collect_files(paying_path($root, $scanRoot)) as $file) {
        $contents = (string) file_get_contents($file);
        if (preg_match('/\bPaymentPayment[A-Za-z0-9_]*/', $contents) === 1) {
            $errors[] = 'Double Payment prefix in file contents: ' . paying_relative($root, $file);
        }
    }
}

echo "Paying release-candidate structure report\n";
echo "========================================\n";
echo 'Status: ' . ($errors === [] ? 'OK' : 'FAILED') . "\n";
echo 'Errors: ' . count($errors) . "\n";
echo 'Warnings: ' . count($warnings) . "\n";

foreach ($warnings as $warning) {
    echo '[WARN] ' . $warning . "\n";
}

foreach ($errors as $error) {
    echo '[ERROR] ' . $error . "\n";
}

exit($errors === [] ? 0 : 1);
