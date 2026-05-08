<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function paying_collect_php_files(string $directory): array
{
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

function paying_relative(string $root, string $absolute): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $normalizedAbsolute = str_replace('\\', '/', $absolute);

    if (str_starts_with($normalizedAbsolute, $normalizedRoot)) {
        return substr($normalizedAbsolute, strlen($normalizedRoot));
    }

    return $normalizedAbsolute;
}

function paying_class_short_name(string $contents): ?string
{
    if (preg_match('/\bclass\s+([A-Za-z_][A-Za-z0-9_]*)\b/', $contents, $matches) !== 1) {
        return null;
    }

    return $matches[1];
}

function paying_namespace(string $contents): ?string
{
    if (preg_match('/\bnamespace\s+([^;]+);/', $contents, $matches) !== 1) {
        return null;
    }

    return trim($matches[1]);
}

function paying_table_name(string $contents): ?string
{
    if (preg_match('/#\[ORM\\\\Table\s*\([^)]*name\s*:\s*[\'"]([^\'"]+)[\'"]/s', $contents, $matches) === 1) {
        return $matches[1];
    }

    if (preg_match('/#\[ORM\\\\Entity\b/s', $contents) === 1) {
        return null;
    }

    return null;
}

function paying_repository_class_name(string $contents): ?string
{
    if (preg_match('/#\[ORM\\\\Entity\s*\([^)]*repositoryClass\s*:\s*([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)::class/s', $contents, $matches) === 1) {
        return str_replace('\\\\', '\\', $matches[1]);
    }

    return null;
}

$errors = [];
$warnings = [];

$entityFiles = paying_collect_php_files(paying_path($root, 'src/Entity'));
$repositoryFiles = paying_collect_php_files(paying_path($root, 'src/Repository'));
$repositoryInterfaceFiles = paying_collect_php_files(paying_path($root, 'src/RepositoryInterface'));
$formFiles = paying_collect_php_files(paying_path($root, 'src/Form'));

$entityCount = 0;
$mappedTableCount = 0;
$repositoryLinkedEntities = 0;
$paymentNamedRepositories = 0;
$paymentNamedRepositoryInterfaces = 0;
$paymentNamedForms = 0;

foreach ($entityFiles as $file) {
    $relative = paying_relative($root, $file);
    $contents = (string) file_get_contents($file);

    if (preg_match('/#\[ORM\\\\Entity\b/s', $contents) !== 1) {
        continue;
    }

    ++$entityCount;

    $className = paying_class_short_name($contents);
    if ($className === null) {
        $errors[] = 'Entity file does not declare a class: ' . $relative;
        continue;
    }

    if (!str_starts_with($className, 'Payment')) {
        $errors[] = 'Doctrine entity class is missing Payment prefix: ' . $relative . ' declares ' . $className;
    }

    $tableName = paying_table_name($contents);
    if ($tableName === null) {
        $warnings[] = 'Doctrine entity has no explicit ORM table name: ' . $relative;
    } else {
        ++$mappedTableCount;

        if ($tableName !== 'payment' && !str_starts_with($tableName, 'payment_')) {
            $errors[] = 'Doctrine entity table does not use payment/payment_* prefix: ' . $relative . ' maps ' . $tableName;
        }
    }

    $repositoryClass = paying_repository_class_name($contents);
    if ($repositoryClass !== null) {
        ++$repositoryLinkedEntities;

        if (!str_contains($repositoryClass, 'Payment')) {
            $errors[] = 'Entity repositoryClass is missing Payment name-form: ' . $relative . ' uses ' . $repositoryClass;
        }

        $repositoryShortName = substr($repositoryClass, strrpos($repositoryClass, '\\') === false ? 0 : strrpos($repositoryClass, '\\') + 1);
        $repositoryPath = paying_path($root, 'src/Repository/' . $repositoryShortName . '.php');

        if (!is_file($repositoryPath)) {
            $warnings[] = 'Entity repositoryClass target file was not found under src/Repository: ' . $relative . ' uses ' . $repositoryClass;
        }
    }
}

foreach ($repositoryFiles as $file) {
    $relative = paying_relative($root, $file);
    $contents = (string) file_get_contents($file);
    $className = paying_class_short_name($contents);

    if ($className === null) {
        continue;
    }

    if (str_contains($className, 'Payment')) {
        ++$paymentNamedRepositories;
    } elseif (str_ends_with($className, 'Repository')) {
        $errors[] = 'Repository class is missing Payment name-form: ' . $relative . ' declares ' . $className;
    }

    if (str_ends_with($className, 'Repository') && !str_contains($contents, 'ServiceEntityRepository')) {
        $warnings[] = 'Repository does not appear to extend/use Doctrine ServiceEntityRepository: ' . $relative;
    }
}

foreach ($repositoryInterfaceFiles as $file) {
    $relative = paying_relative($root, $file);
    $contents = (string) file_get_contents($file);

    if (preg_match('/\binterface\s+([A-Za-z_][A-Za-z0-9_]*)\b/', $contents, $matches) !== 1) {
        continue;
    }

    $interfaceName = $matches[1];
    if (str_contains($interfaceName, 'Payment')) {
        ++$paymentNamedRepositoryInterfaces;
    } elseif (str_ends_with($interfaceName, 'RepositoryInterface')) {
        $errors[] = 'Repository interface is missing Payment name-form: ' . $relative . ' declares ' . $interfaceName;
    }
}

foreach ($formFiles as $file) {
    $relative = paying_relative($root, $file);
    $contents = (string) file_get_contents($file);
    $className = paying_class_short_name($contents);

    if ($className === null || !str_ends_with($className, 'Type')) {
        continue;
    }

    if (str_contains($className, 'Payment')) {
        ++$paymentNamedForms;
    } else {
        $errors[] = 'Symfony form type is missing Payment name-form: ' . $relative . ' declares ' . $className;
    }

    if (!str_contains($contents, 'AbstractType')) {
        $warnings[] = 'Symfony form type does not appear to extend/use AbstractType: ' . $relative;
    }
}

echo "Paying entity-first consistency report\n";
echo "======================================\n";
echo 'Doctrine entity files scanned: ' . count($entityFiles) . "\n";
echo 'Doctrine entities detected: ' . $entityCount . "\n";
echo 'Explicit mapped tables detected: ' . $mappedTableCount . "\n";
echo 'Entities with repositoryClass detected: ' . $repositoryLinkedEntities . "\n";
echo 'Repository files scanned: ' . count($repositoryFiles) . "\n";
echo 'Payment-named repositories detected: ' . $paymentNamedRepositories . "\n";
echo 'Repository interface files scanned: ' . count($repositoryInterfaceFiles) . "\n";
echo 'Payment-named repository interfaces detected: ' . $paymentNamedRepositoryInterfaces . "\n";
echo 'Form files scanned: ' . count($formFiles) . "\n";
echo 'Payment-named form types detected: ' . $paymentNamedForms . "\n";
echo 'Warnings: ' . count($warnings) . "\n";
echo 'Errors: ' . count($errors) . "\n";

foreach ($warnings as $warning) {
    echo '[WARN] ' . $warning . "\n";
}

if ($errors !== []) {
    echo "Status: FAILED\n";
    foreach ($errors as $error) {
        echo '- ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
