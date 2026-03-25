<?php

declare(strict_types=1);

$files = [
    'var/payment.test.data.sqlite',
    'var/payment.test.infra.sqlite',
];

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

$cacheDir = 'var/cache/test';
if (is_dir($cacheDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();
        if ($item->isDir()) {
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    rmdir($cacheDir);
}

echo "Test sqlite files and test cache reset\n";
