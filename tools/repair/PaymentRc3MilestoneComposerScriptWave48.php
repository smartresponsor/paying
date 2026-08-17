<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerPath = $root . DIRECTORY_SEPARATOR . 'composer.json';

$contents = (string) file_get_contents($composerPath);
if (str_starts_with($contents, "\xEF\xBB\xBF")) {
    $contents = substr($contents, 3);
}

$composer = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

if (!isset($composer['scripts']) || !is_array($composer['scripts'])) {
    $composer['scripts'] = [];
}

$composer['scripts']['report:rc3-milestone'] = '@php tools/php/php84.php tools/inspection/PayingRc3MilestoneReport.php';

if (!isset($composer['description']) || !is_string($composer['description']) || trim($composer['description']) === '') {
    $description = 'Symfony payment component for payment lifecycle, providers, webhooks, reconciliation, and operational payment workflows.';
    $ordered = [];

    foreach ($composer as $key => $value) {
        $ordered[$key] = $value;

        if ($key === 'name') {
            $ordered['description'] = $description;
        }
    }

    $composer = $ordered;
}

file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");

echo "Registered composer script: report:rc3-milestone\n";