<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$src = $root . DIRECTORY_SEPARATOR . 'src';

if (!is_dir($src)) {
    fwrite(STDERR, "src directory not found\n");
    exit(2);
}

$violations = [];
$summary = [
    'php_files' => 0,
    'namespace_scope' => [],
    'root_loose_files' => [],
];

$allowedRootFiles = [
    '.editorconfig', '.env', '.env.example', '.env.test', '.gitattributes', '.gitignore',
    '.gitleaks.toml', '.mcp.json', '.php-cs-fixer.dist.php', '.php-cs-fixer.php', '.yamllint.yml',
    'CHANGELOG.md', 'README.md', 'RELEASE_NOTES.md', 'composer.json', 'composer.lock',
    'compose.yml', 'doctum.php', 'package-lock.json', 'package.json', 'phpstan.neon',
    'phpunit.xml.dist', 'phpunit.xsd', 'qodana.yaml', 'rector.php',
];

foreach (new DirectoryIterator($root) as $entry) {
    if (!$entry->isFile()) {
        continue;
    }

    $name = $entry->getFilename();
    if (!in_array($name, $allowedRootFiles, true)) {
        $summary['root_loose_files'][] = $name;
    }
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
        continue;
    }

    $summary['php_files']++;
    $path = $file->getPathname();
    $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        $violations[] = [$relative, 'Unreadable PHP file.'];
        continue;
    }

    preg_match('/^namespace\s+([^;]+);/m', $contents, $namespaceMatch);
    $namespace = $namespaceMatch[1] ?? '';
    $scope = $namespace === '' ? '<none>' : implode('\\', array_slice(explode('\\', $namespace), 0, 2));
    $summary['namespace_scope'][$scope] = ($summary['namespace_scope'][$scope] ?? 0) + 1;

    if ($namespace === '' || !str_starts_with($namespace, 'App\\Paying')) {
        $violations[] = [$relative, 'Namespace must stay inside App\\Paying.'];
    }

    preg_match('/^(?:final\s+|abstract\s+)?(?:readonly\s+)?(?:class|interface|enum|trait)\s+(\w+)/m', $contents, $classMatch);
    $className = $classMatch[1] ?? basename($relative, '.php');
    $segments = explode(DIRECTORY_SEPARATOR, $relative);
    $layer = $segments[1] ?? '';

    $hasSuffix = static function (string $className, array $suffixes): bool {
        foreach ($suffixes as $suffix) {
            if (str_ends_with($className, $suffix)) {
                return true;
            }
        }

        return false;
    };

    if ($layer === 'Controller' && !str_contains($relative, DIRECTORY_SEPARATOR . 'Dto' . DIRECTORY_SEPARATOR) && !str_ends_with($className, 'Controller')) {
        $violations[] = [$relative, 'Controller class must end with Controller.'];
    }
    if ($layer === 'ControllerInterface' && !str_ends_with($className, 'ControllerInterface')) {
        $violations[] = [$relative, 'Controller interface must end with ControllerInterface.'];
    }
    if ($layer === 'Repository' && !str_ends_with($className, 'Repository')) {
        $violations[] = [$relative, 'Repository class must end with Repository.'];
    }
    if ($layer === 'RepositoryInterface' && !str_ends_with($className, 'RepositoryInterface')) {
        $violations[] = [$relative, 'Repository interface must end with RepositoryInterface.'];
    }
    if ($layer === 'Form' && !str_ends_with($className, 'Type')) {
        $violations[] = [$relative, 'Form class must end with Type.'];
    }
    if ($layer === 'Command' && !str_ends_with($className, 'Command')) {
        $violations[] = [$relative, 'Command class must end with Command.'];
    }
    if ($layer === 'Event' && !str_ends_with($className, 'Event')) {
        $violations[] = [$relative, 'Event class must end with Event.'];
    }
    if ($layer === 'Message' && !$hasSuffix($className, ['Command', 'Event', 'Message', 'Handler', 'Consumer'])) {
        $violations[] = [$relative, 'Message class form must be Command/Event/Message/Handler/Consumer.'];
    }
    if ($layer === 'Service' && !$hasSuffix($className, ['Service', 'Handler', 'Factory', 'Provider', 'Router', 'Guard', 'Verifier', 'Mapper', 'Normalizer', 'Validator', 'Reporter', 'Executor', 'Result', 'Exception', 'PaymentMetric', 'Cache', 'Sync', 'Processor', 'Gateway'])) {
        $violations[] = [$relative, 'Service class form is not type-identifiable.'];
    }
    if ($layer === 'Infrastructure' && !$hasSuffix($className, ['Command', 'Subscriber', 'Repository', 'Store', 'Logger', 'Publisher', 'Worker', 'Log', 'Entity', 'Fixture', 'Faker'])) {
        $violations[] = [$relative, 'Infrastructure class form is not type-identifiable.'];
    }
}

ksort($summary['namespace_scope']);
sort($summary['root_loose_files']);

echo "Paying canonical structure audit\n";
echo "================================\n\n";
echo "PHP files: " . $summary['php_files'] . "\n";
echo "Namespace scopes:\n";
foreach ($summary['namespace_scope'] as $scope => $count) {
    echo "  - {$scope}: {$count}\n";
}
echo "\nLoose root files:\n";
if ($summary['root_loose_files'] === []) {
    echo "  - none\n";
} else {
    foreach ($summary['root_loose_files'] as $file) {
        echo "  - {$file}\n";
    }
}
echo "\nClass-form findings:\n";
if ($violations === []) {
    echo "  - none\n";
} else {
    foreach ($violations as [$file, $message]) {
        echo "  - {$file}: {$message}\n";
    }
}
echo "\nMode: report-only. This script does not rename, move, or delete files.\n";
exit(0);
