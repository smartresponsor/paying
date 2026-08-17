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

if (!is_array($composer)) {
    fwrite(STDERR, "composer.json did not decode to an object.\n");
    exit(1);
}

$description = 'Symfony payment component for payment lifecycle, providers, webhooks, reconciliation, and operational payment workflows.';

if (!isset($composer['description']) || !is_string($composer['description']) || trim($composer['description']) === '') {
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
    echo "Added composer description.\n";
} else {
    echo "Composer description already present.\n";
}

$encoded = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
file_put_contents($composerPath, $encoded . "\n");

echo "composer.json normalized as UTF-8 without BOM.\n";
