<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$issues = [];
$canonicalPath = $root.'/src/Attribute/PaymentRequireScopeAttribute.php';
$legacyPath = $root.'/src/Attribute/RequireScope.php';

if (!is_file($canonicalPath)) {
    $issues[] = 'Missing canonical attribute: src/Attribute/PaymentRequireScopeAttribute.php';
}

if (is_file($legacyPath)) {
    $issues[] = 'Legacy unprefixed attribute remains: src/Attribute/RequireScope.php';
}

$scanRoots = [
    $root.'/src',
    $root.'/tests',
];

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
        $contents = file_get_contents($path);
        if ($contents === false) {
            $issues[] = 'Unable to read PHP file: '.$relative;
            continue;
        }

        if (str_contains($contents, 'Attribute\RequireScope') || str_contains($contents, '#[RequireScope(') || str_contains($contents, 'RequireScope::class')) {
            $issues[] = 'Legacy RequireScope reference remains in '.$relative;
        }

        if (preg_match('/final\s+class\s+RequireScope\b/', $contents) === 1) {
            $issues[] = 'Legacy RequireScope class declaration remains in '.$relative;
        }
    }
}

if ($issues !== []) {
    echo "Paying attribute nameEntity-form report: FAILED\n";
    foreach ($issues as $issue) {
        echo '- '.$issue."\n";
    }
    exit(1);
}

echo "Paying attribute nameEntity-form report: OK\n";
echo "Canonical attribute: App\Paying\Attribute\PaymentRequireScopeAttribute\n";