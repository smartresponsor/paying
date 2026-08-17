<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerPath = $root . DIRECTORY_SEPARATOR . 'composer.json';

if (!is_file($composerPath)) {
    fwrite(STDERR, "composer.json was not found.\n");
    exit(1);
}

$contents = (string) file_get_contents($composerPath);
if (str_starts_with($contents, "\xEF\xBB\xBF")) {
    $contents = substr($contents, 3);
}

try {
    $composer = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    fwrite(STDERR, 'composer.json is not valid JSON: ' . $exception->getMessage() . "\n");
    exit(1);
}

if (!isset($composer['scripts']) || !is_array($composer['scripts'])) {
    $composer['scripts'] = [];
}

$composer['scripts']['report:runtime-proof-closure'] = '@php tools/php/php84.php tools/inspection/PayingRuntimeProofClosureReport.php';

if (!isset($composer['description']) || !is_string($composer['description']) || trim($composer['description']) === '') {
    $description = 'Symfony payment component for payment lifecycle, providers, webhooks, reconciliation, and operational payment workflows.';
    $ordered = [];

    foreach ($composer as $key => $value) {
        $ordered[$key] = $value;

        if ($key === 'name') {
            $ordered['description'] = $description;
        }
    }

    if (!array_key_exists('description', $ordered)) {
        $ordered = ['description' => $description] + $composer;
    }

    $composer = $ordered;
}

$encoded = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
file_put_contents($composerPath, $encoded . "\n");

echo "Registered composer script: report:runtime-proof-closure\n";
echo "composer.json normalized as UTF-8 without BOM.\n";
