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

$composer = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

if (!isset($composer['scripts']) || !is_array($composer['scripts'])) {
    $composer['scripts'] = [];
}

$composer['scripts']['report:rc3-handoff'] = '@php tools/php/php84.php tools/inspection/PayingRc3HandoffReport.php';

$encoded = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
file_put_contents($composerPath, $encoded . "\n");

echo "Registered composer script: report:rc3-handoff\n";
