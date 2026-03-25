<?php

declare(strict_types=1);

$roots = ['src', 'tests', 'config', 'tools'];
$files = [];
foreach ($roots as $root) {
    if (!is_dir($root)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}
sort($files);
$failed = [];
foreach ($files as $file) {
    $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1';
    exec($cmd, $output, $exitCode);
    if ($exitCode !== 0) {
        $failed[$file] = implode(PHP_EOL, $output);
    }
    $output = [];
}
if ($failed !== []) {
    foreach ($failed as $file => $message) {
        fwrite(STDERR, '[FAIL] ' . $file . PHP_EOL . $message . PHP_EOL);
    }
    exit(1);
}
echo 'PHP lint passed for ' . count($files) . ' files.' . PHP_EOL;
