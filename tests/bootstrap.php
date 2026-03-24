<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$projectDir = dirname(__DIR__);
$sourceRoot = $projectDir.'/src';
$legacyHoldingRoot = $projectDir.'/var/legacy-disabled-src';
$legacyPhpPaths = [];

$moveToQuarantine = static function (string $pathname, string $projectDir, string $legacyHoldingRoot, string $reason) use (&$legacyPhpPaths): void {
    $relative = ltrim(str_replace($projectDir, '', $pathname), DIRECTORY_SEPARATOR);
    $target = $legacyHoldingRoot.DIRECTORY_SEPARATOR.$relative;

    if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0777, true) && !is_dir(dirname($target))) {
        throw new RuntimeException(sprintf('Failed to create legacy holding directory for "%s".', $relative));
    }

    if (file_exists($target)) {
        $target .= '__'.uniqid('', true);
    }

    if (!rename($pathname, $target)) {
        throw new RuntimeException(sprintf('Failed to quarantine "%s".', $relative));
    }

    $legacyPhpPaths[] = [$relative, ltrim(str_replace($projectDir, '', $target), DIRECTORY_SEPARATOR), $reason];
};

$extractDeclaredSymbol = static function (string $pathname): ?string {
    if (!is_file($pathname) || 'php' !== strtolower(pathinfo($pathname, PATHINFO_EXTENSION))) {
        return null;
    }

    $code = file_get_contents($pathname);
    if (false === $code) {
        return null;
    }

    $tokens = token_get_all($code);
    $namespace = '';
    $count = count($tokens);

    for ($index = 0; $index < $count; ++$index) {
        $token = $tokens[$index];
        if (!is_array($token)) {
            continue;
        }

        if (T_NAMESPACE === $token[0]) {
            $namespace = '';
            for ($j = $index + 1; $j < $count; ++$j) {
                $part = $tokens[$j];
                if (is_string($part) && (';' === $part || '{' === $part)) {
                    break;
                }
                if (is_array($part) && in_array($part[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
                    $namespace .= $part[1];
                }
            }
            continue;
        }

        if (!in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
            continue;
        }

        $prev = $tokens[$index - 1] ?? null;
        if (is_array($prev) && T_DOUBLE_COLON === $prev[0]) {
            continue;
        }

        for ($j = $index + 1; $j < $count; ++$j) {
            $next = $tokens[$j];
            if (is_array($next) && T_STRING === $next[0]) {
                $name = $next[1];

                return ltrim($namespace.'\\'.$name, '\\');
            }
        }
    }

    return null;
};

$scoreCanonicalPath = static function (string $pathname): array {
    $normalized = str_replace('\\', '/', $pathname);
    $score = 0;
    if (str_contains($normalized, '/src/Entity/')) {
        $score -= 50;
    }
    if (1 === preg_match('#/[^/]+\.php/#', $normalized)) {
        $score += 100;
    }
    $depth = substr_count(trim($normalized, '/'), '/');

    return [$score, $depth, strlen($normalized), $normalized];
};

if (is_dir($sourceRoot)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if (!$item->isDir()) {
            continue;
        }

        $pathname = $item->getPathname();
        $relative = ltrim(str_replace($projectDir, '', $pathname), DIRECTORY_SEPARATOR);
        $segments = preg_split('#[\\/]#', $relative) ?: [];

        foreach ($segments as $segment) {
            if (!str_ends_with($segment, '.php')) {
                continue;
            }

            $moveToQuarantine($pathname, $projectDir, $legacyHoldingRoot, 'legacy-php-path-directory');
            continue 2;
        }
    }

    $classMap = [];
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($rii as $item) {
        if (!$item->isFile()) {
            continue;
        }

        $pathname = $item->getPathname();
        $symbol = $extractDeclaredSymbol($pathname);
        if (null === $symbol) {
            continue;
        }

        $classMap[strtolower($symbol)][] = $pathname;
    }

    foreach ($classMap as $symbol => $paths) {
        if (count($paths) < 2) {
            continue;
        }

        usort($paths, static fn (string $left, string $right): int => $scoreCanonicalPath($left) <=> $scoreCanonicalPath($right));
        $preferred = array_shift($paths);

        foreach ($paths as $pathname) {
            $moveToQuarantine(
                $pathname,
                $projectDir,
                $legacyHoldingRoot,
                'duplicate-symbol:'.$symbol.' preferred='.ltrim(str_replace($projectDir, '', $preferred), DIRECTORY_SEPARATOR)
            );
        }
    }
}

if ([] !== $legacyPhpPaths) {
    fwrite(STDERR, "Quarantined legacy PHP-path directories/files before test bootstrap:\n");
    foreach ($legacyPhpPaths as [$from, $to, $reason]) {
        fwrite(STDERR, sprintf(" - %s -> %s [%s]\n", $from, $to, $reason));
    }
}

$autoload = $projectDir.'/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Autoloader not found. Run composer install before executing tests.\n");
    exit(1);
}

require $autoload;
