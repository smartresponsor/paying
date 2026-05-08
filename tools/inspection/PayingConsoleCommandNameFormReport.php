<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$consoleRoot = $root . '/src/Infrastructure/Console';

$violations = [];

if (!is_dir($consoleRoot)) {
    fwrite(STDERR, "Missing console command root: {$consoleRoot}\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($consoleRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
        continue;
    }

    $relativePath = str_replace($root . '/', '', $file->getPathname());
    $contents = (string) file_get_contents($file->getPathname());

    if (!preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+Command/', $contents, $match)) {
        continue;
    }

    $className = $match[1];

    if (!str_starts_with($className, 'Payment')) {
        $violations[] = sprintf('%s declares non-prefixed console command class %s', $relativePath, $className);
    }

    if (!str_starts_with($file->getBasename('.php'), 'Payment')) {
        $violations[] = sprintf('%s uses non-prefixed console command file name', $relativePath);
    }

    if (str_starts_with($className, 'PaymentPayment')) {
        $violations[] = sprintf('%s declares doubled Payment prefix class %s', $relativePath, $className);
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Paying console command name-form report: FAIL\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }
    exit(1);
}

fwrite(STDOUT, "Paying console command name-form report: OK\n");
