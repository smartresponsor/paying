<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * Report-only guard for Paying entity-first persistence topology.
 */
final class PayingEntityFirstPersistenceReport
{
    private const ENTITY_DIRS = ['src/Entity', 'src/Infrastructure/Entity'];
    private const TABLE_PREFIX = 'payment';

    /** @var list<string> */
    private array $errors = [];
    /** @var list<string> */
    private array $warnings = [];
    /** @var list<string> */
    private array $entityFiles = [];
    /** @var array<string, string> */
    private array $entityTables = [];

    public static function main(): int
    {
        return (new self())->run(getcwd());
    }

    private function run(string $root): int
    {
        $this->scanEntities($root);
        $this->scanMigrations($root);
        $this->printReport();

        return [] === $this->errors ? 0 : 1;
    }

    private function scanEntities(string $root): void
    {
        foreach (self::ENTITY_DIRS as $dir) {
            $absoluteDir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);

            if (!is_dir($absoluteDir)) {
                $this->errors[] = sprintf('Missing entity directory: %s', $dir);
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absoluteDir, FilesystemIterator::SKIP_DOTS));

            foreach ($iterator as $file) {
                if (!$file instanceof SplFileInfo || 'php' !== $file->getExtension()) {
                    continue;
                }

                $relative = $this->relativePath($root, $file->getPathname());
                $this->entityFiles[] = $relative;
                $this->inspectEntityFile($relative, $file->getPathname());
            }
        }

        sort($this->entityFiles);

        if ([] === $this->entityFiles) {
            $this->errors[] = 'No Doctrine entity files found in src/Entity or src/Infrastructure/Entity.';
        }
    }

    private function inspectEntityFile(string $relative, string $absolute): void
    {
        $basename = basename($relative, '.php');
        $contents = (string) file_get_contents($absolute);

        if (!str_starts_with($basename, 'Payment')) {
            $this->errors[] = sprintf('Entity class file must start with Payment*: %s', $relative);
        }

        if (!str_ends_with($basename, 'Entity')) {
            $this->errors[] = sprintf('Entity class file must end with *Entity: %s', $relative);
        }

        if (!str_contains($contents, '#[ORM\\Entity]')) {
            $this->errors[] = sprintf('Doctrine entity attribute missing: %s', $relative);
        }

        if (!preg_match('/namespace\s+App\\\\Paying\\\\(?:Infrastructure\\\\)?Entity;/', $contents)) {
            $this->errors[] = sprintf('Entity namespace must stay under App\\Paying\\Entity or App\\Paying\\Infrastructure\\Entity: %s', $relative);
        }

        if (!preg_match("/#\[ORM\\\\Table\(name:\s*'([^']+)'\)\]/", $contents, $match)) {
            $this->errors[] = sprintf('Doctrine table attribute with explicit name is missing: %s', $relative);
            return;
        }

        $table = $match[1];
        $this->entityTables[$relative] = $table;

        if ($table !== self::TABLE_PREFIX && !str_starts_with($table, self::TABLE_PREFIX . '_')) {
            $this->errors[] = sprintf('Table name must use payment prefix: %s -> %s', $relative, $table);
        }
    }

    private function scanMigrations(string $root): void
    {
        $migrationsDir = $root . DIRECTORY_SEPARATOR . 'Migrations';

        if (!is_dir($migrationsDir)) {
            $this->warnings[] = 'Migrations directory is absent; persistence proof depends on Doctrine mapping only.';
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($migrationsDir, FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || 'php' !== $file->getExtension()) {
                continue;
            }

            $relative = $this->relativePath($root, $file->getPathname());
            $contents = (string) file_get_contents($file->getPathname());

            preg_match_all('/(?:CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|DROP\s+TABLE(?:\s+IF\s+EXISTS)?|hasTable|createTable|dropTable)\s*\(?[\'\"]([a-zA-Z0-9_]+)[\'\"]?/i', $contents, $matches);

            foreach ($matches[1] ?? [] as $table) {
                if ($table === self::TABLE_PREFIX || str_starts_with($table, self::TABLE_PREFIX . '_')) {
                    continue;
                }

                $this->errors[] = sprintf('Migration table reference must use payment prefix: %s -> %s', $relative, $table);
            }
        }
    }

    private function printReport(): void
    {
        echo "Paying entity-first persistence report\n";
        echo "======================================\n";
        echo sprintf("Entities scanned: %d\n", count($this->entityFiles));
        echo sprintf("Tables mapped: %d\n", count($this->entityTables));

        foreach ($this->entityTables as $file => $table) {
            echo sprintf(" - %s => %s\n", $file, $table);
        }

        if ([] !== $this->warnings) {
            echo "\nWarnings:\n";
            foreach ($this->warnings as $warning) {
                echo sprintf(" - %s\n", $warning);
            }
        }

        if ([] !== $this->errors) {
            echo "\nErrors:\n";
            foreach ($this->errors as $error) {
                echo sprintf(" - %s\n", $error);
            }
            return;
        }

        echo "\nStatus: OK\n";
    }

    private function relativePath(string $root, string $path): string
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, $root . '/')) {
            return substr($path, strlen($root) + 1);
        }

        return $path;
    }
}

exit(PayingEntityFirstPersistenceReport::main());
