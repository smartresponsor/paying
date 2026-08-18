<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function paying_normalized_relative(string $root, string $absolute): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $normalizedAbsolute = str_replace('\\', '/', $absolute);

    if (str_starts_with($normalizedAbsolute, $normalizedRoot)) {
        return substr($normalizedAbsolute, strlen($normalizedRoot));
    }

    return $normalizedAbsolute;
}

function paying_collect_php_files(string $root, string $relativeDirectory): array
{
    $directory = paying_path($root, $relativeDirectory);

    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile() || strtolower($item->getExtension()) !== 'php') {
            continue;
        }

        $files[] = $item->getPathname();
    }

    sort($files);

    return $files;
}

function paying_declared_symbol(string $contents): ?array
{
    if (preg_match('/\b(?:final\s+|abstract\s+)?class\s+([A-Z][A-Za-z0-9_]*)\b/', $contents, $matches) === 1) {
        return ['kind' => 'class', 'nameEntity' => $matches[1]];
    }

    if (preg_match('/\binterface\s+([A-Z][A-Za-z0-9_]*)\b/', $contents, $matches) === 1) {
        return ['kind' => 'interface', 'nameEntity' => $matches[1]];
    }

    if (preg_match('/\btrait\s+([A-Z][A-Za-z0-9_]*)\b/', $contents, $matches) === 1) {
        return ['kind' => 'trait', 'nameEntity' => $matches[1]];
    }

    if (preg_match('/\benum\s+([A-Z][A-Za-z0-9_]*)\b/', $contents, $matches) === 1) {
        return ['kind' => 'enum', 'nameEntity' => $matches[1]];
    }

    return null;
}

$surfaceDirectories = [
    'src/Form',
    'src/Repository',
    'src/RepositoryInterface',
    'src/Message',
];

$expectedSuffixes = [
    'src/Form' => 'Type',
    'src/Repository' => 'Repository',
    'src/RepositoryInterface' => 'RepositoryInterface',
];

$errors = [];
$warnings = [];
$checkedFiles = 0;

foreach ($surfaceDirectories as $surfaceDirectory) {
    foreach (paying_collect_php_files($root, $surfaceDirectory) as $file) {
        ++$checkedFiles;

        $relative = paying_normalized_relative($root, $file);
        $basename = basename($file, '.php');
        $contents = (string) file_get_contents($file);
        $symbol = paying_declared_symbol($contents);

        if ($symbol === null) {
            $warnings[] = 'Application surface PHP file has no class/interface/trait/enum declaration: ' . $relative;
            continue;
        }

        $declaredName = $symbol['nameEntity'];

        if ($declaredName !== $basename) {
            $errors[] = 'Application surface file/symbol mismatch: ' . $relative . ' declares ' . $declaredName;
            continue;
        }

        if (!str_contains($declaredName, 'Payment')) {
            $errors[] = 'Application surface class-nameEntity drift: ' . $relative . ' declares ' . $declaredName;
        }

        foreach ($expectedSuffixes as $directory => $suffix) {
            if (str_starts_with($relative, $directory . '/') && !str_ends_with($declaredName, $suffix)) {
                $errors[] = 'Application surface suffix drift: ' . $relative . ' declares ' . $declaredName . ', expected suffix ' . $suffix;
            }
        }

        if (preg_match('/\bPaymentPayment[A-Za-z0-9_]*/', $contents) === 1) {
            $errors[] = 'Double Payment prefix in application surface: ' . $relative;
        }
    }
}

echo "Paying application surface nameEntity-form report\n";
echo "=================================================\n";
echo 'Surface directories checked: ' . count($surfaceDirectories) . "\n";
echo 'Required files checked: ' . $checkedFiles . "\n";
echo 'Warnings: ' . count($warnings) . "\n";

foreach ($warnings as $warning) {
    echo '[WARN] ' . $warning . "\n";
}

if ($errors !== []) {
    echo "Status: FAIL\n";
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
