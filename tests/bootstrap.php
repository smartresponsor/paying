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

if ('1' === (string) ($_SERVER['PAYMENT_TEST_QUARANTINE_LEGACY'] ?? $_ENV['PAYMENT_TEST_QUARANTINE_LEGACY'] ?? '0') && is_dir($sourceRoot)) {
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

$deleteDirectoryTree = static function (string $directory): void {
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
            continue;
        }

        unlink($item->getPathname());
    }

    rmdir($directory);
};

$deleteDirectoryTree($projectDir.'/var/cache/test');

$autoload = $projectDir.'/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Autoloader not found. Run composer install before executing tests.\n");
    exit(1);
}

require $autoload;

if (class_exists(\Symfony\Component\Dotenv\Dotenv::class)) {
    (new \Symfony\Component\Dotenv\Dotenv())->usePutenv(true)->bootEnv($projectDir.'/.env');
}

$resolveSqlitePath = static function (?string $url, string $projectDir): ?string {
    if (null === $url || '' === $url) {
        return null;
    }

    $resolved = str_replace('%kernel.project_dir%', $projectDir, $url);
    if (!str_starts_with($resolved, 'sqlite:///')) {
        return null;
    }

    $path = substr($resolved, strlen('sqlite:///'));
    if (false === $path || '' === $path) {
        return null;
    }

    if (DIRECTORY_SEPARATOR === '\\' && 1 === preg_match('#^/[A-Za-z]:/#', $path)) {
        $path = ltrim($path, '/');
    }

    return $path;
};

$ensureSqliteSchema = static function (string $path, array $statements): void {
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException(sprintf('Failed to create sqlite directory for "%s".', $path));
    }

    $pdo = new PDO('sqlite:'.$path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
};

if ('test' === ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null)) {
    $dataPath = $resolveSqlitePath($_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? null, $projectDir);
    $infraPath = $resolveSqlitePath($_SERVER['INFRA_URL'] ?? $_ENV['INFRA_URL'] ?? null, $projectDir);

    if (null !== $dataPath) {
        $ensureSqliteSchema($dataPath, [
            'CREATE TABLE IF NOT EXISTS payment (id VARCHAR(26) NOT NULL PRIMARY KEY, status VARCHAR(16) NOT NULL, amount NUMERIC(14,2) NOT NULL, currency VARCHAR(3) NOT NULL, provider_ref VARCHAR(128) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)',
            'CREATE UNIQUE INDEX IF NOT EXISTS uniq_payment_id ON payment (id)',
            'CREATE INDEX IF NOT EXISTS idx_payment_status_updated_at ON payment (status, updated_at)',
            'CREATE TABLE IF NOT EXISTS payment_transaction (id VARCHAR(36) NOT NULL PRIMARY KEY, payment_id VARCHAR(26) NOT NULL, gateway_transaction_id VARCHAR(64) NOT NULL, type VARCHAR(16) NOT NULL, amount_minor INTEGER NOT NULL, occurred_at DATETIME NOT NULL)',
            'CREATE INDEX IF NOT EXISTS idx_payment_transaction_payment_id ON payment_transaction (payment_id)',
            'CREATE TABLE IF NOT EXISTS payment_refund (id VARCHAR(36) NOT NULL PRIMARY KEY, payment_id VARCHAR(26) NOT NULL, amount_minor INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, reason VARCHAR(64) DEFAULT NULL, refunded_at DATETIME NOT NULL)',
            'CREATE INDEX IF NOT EXISTS idx_payment_refund_payment_id ON payment_refund (payment_id)',
            "CREATE TABLE IF NOT EXISTS payment_outbox_message (id VARCHAR(36) NOT NULL PRIMARY KEY, type VARCHAR(128) NOT NULL, payload TEXT NOT NULL, occurred_at DATETIME NOT NULL, status VARCHAR(32) NOT NULL DEFAULT 'pending', attempts INTEGER NOT NULL DEFAULT 0, last_error TEXT DEFAULT NULL, routing_key VARCHAR(128) DEFAULT NULL)",
            'CREATE INDEX IF NOT EXISTS idx_payment_outbox_message_status_occurred_at ON payment_outbox_message (status, occurred_at)',
            'CREATE TABLE IF NOT EXISTS payment_dlq (id INTEGER PRIMARY KEY AUTOINCREMENT, outbox_id VARCHAR(36) NOT NULL, topic VARCHAR(120) NOT NULL, payload TEXT NOT NULL, reason VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL)',
            'CREATE INDEX IF NOT EXISTS idx_payment_dlq_outbox_id ON payment_dlq (outbox_id)',
            'CREATE TABLE IF NOT EXISTS payment_idempotency (key VARCHAR(80) NOT NULL PRIMARY KEY, value TEXT NOT NULL, expires_at DATETIME NOT NULL)',
            'CREATE TABLE IF NOT EXISTS payment_circuit (key VARCHAR(80) NOT NULL PRIMARY KEY, failure_count INTEGER NOT NULL, retry_at DATETIME NOT NULL)',
            'CREATE TABLE IF NOT EXISTS payment_webhook_log (id VARCHAR(36) NOT NULL PRIMARY KEY, provider VARCHAR(32) NOT NULL, external_event_id VARCHAR(191) NOT NULL, payload TEXT NOT NULL, status VARCHAR(16) NOT NULL, duplicate_count INTEGER NOT NULL DEFAULT 0, received_at DATETIME NOT NULL, processed_at DATETIME DEFAULT NULL)',
            'CREATE UNIQUE INDEX IF NOT EXISTS uniq_payment_webhook_provider_event ON payment_webhook_log (provider, external_event_id)',
            'CREATE INDEX IF NOT EXISTS idx_payment_webhook_status ON payment_webhook_log (status)',
            'CREATE INDEX IF NOT EXISTS idx_payment_webhook_received_at ON payment_webhook_log (received_at)',
        ]);
    }

    if (null !== $infraPath) {
        $ensureSqliteSchema($infraPath, [
            'CREATE TABLE IF NOT EXISTS payment_projection (id VARCHAR(26) NOT NULL PRIMARY KEY, status VARCHAR(16) NOT NULL, amount NUMERIC(14,2) NOT NULL, currency VARCHAR(3) NOT NULL, provider_ref VARCHAR(128) DEFAULT NULL, updated_at DATETIME NOT NULL)',
            'CREATE INDEX IF NOT EXISTS idx_payment_projection_status ON payment_projection (status)',
            'CREATE INDEX IF NOT EXISTS idx_payment_projection_updated_at ON payment_projection (updated_at)',
            'CREATE TABLE IF NOT EXISTS payment_projection_meta (name VARCHAR(64) NOT NULL PRIMARY KEY, value TEXT NOT NULL)',
        ]);
    }
}
