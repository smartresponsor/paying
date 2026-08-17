<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controllerDir = $root.'/src/Controller';
$interfaceDir = $root.'/src/ControllerInterface';

$allowedSubdirectories = [
    'Dto',
    'Webhook',
];

$violations = [];

foreach ([$controllerDir, $interfaceDir] as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $relative = str_replace($root.'/', '', $file->getPathname());
        $parts = explode('/', $relative);

        if (isset($parts[2]) && in_array($parts[2], $allowedSubdirectories, true)) {
            continue;
        }

        $basename = $file->getBasename('.php');
        if (!str_starts_with($basename, 'Payment')) {
            $violations[] = $relative;
        }
    }
}

if ($violations === []) {
    echo "Paying controller name-form report: OK\n";
    exit(0);
}

echo "Paying controller name-form report: violations found\n";
foreach ($violations as $violation) {
    echo " - {$violation}\n";
}

exit(1);
