<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$forbidden = [
    'src/ValueObject/Money.php',
    'src/ValueObject/GatewayCode.php',
    'src/ValueObject/TransactionId.php',
    'src/Exception/OutboxOperationException.php',
    'tests/Unit/ValueObject/MoneyTest.php',
];

$required = [
    'src/ValueObject/PaymentMoney.php' => 'final readonly class PaymentMoney',
    'src/ValueObject/PaymentGatewayCode.php' => 'final class PaymentGatewayCode',
    'src/ValueObject/PaymentTransactionId.php' => 'final readonly class PaymentTransactionId',
    'src/Exception/PaymentOutboxOperationException.php' => 'final class PaymentOutboxOperationException',
    'tests/Unit/ValueObject/PaymentMoneyTest.php' => 'final class PaymentMoneyTest',
];

$errors = [];

foreach ($forbidden as $path) {
    if (is_file($root.'/'.$path)) {
        $errors[] = 'Legacy value-object/exception file still exists: '.$path;
    }
}

foreach ($required as $path => $needle) {
    $absolute = $root.'/'.$path;
    if (!is_file($absolute)) {
        $errors[] = 'Missing canonical value-object/exception file: '.$path;
        continue;
    }

    $contents = file_get_contents($absolute);
    if (false === $contents || !str_contains($contents, $needle)) {
        $errors[] = 'Canonical file does not expose expected symbol: '.$path.' => '.$needle;
    }
}

$scanRoots = ['src', 'tests'];
$legacyRegexes = [
    '/use App\\\\Paying\\\\ValueObject\\\\Money;/',
    '/use App\\\\Paying\\\\ValueObject\\\\GatewayCode;/',
    '/use App\\\\Paying\\\\ValueObject\\\\TransactionId;/',
    '/use App\\\\Paying\\\\Exception\\\\OutboxOperationException;/',
    '/(?<!Payment)\\bMoney::/',
    '/new\\s+Money\\s*\\(/',
    '/new\\s+GatewayCode\\s*\\(/',
    '/new\\s+TransactionId\\s*\\(/',
    '/new\\s+OutboxOperationException\\s*\\(/',
];

foreach ($scanRoots as $relativeRoot) {
    $directory = $root.'/'.$relativeRoot;
    if (!is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || 'php' !== $file->getExtension()) {
            continue;
        }

        $relativePath = str_replace($root.'/', '', $file->getPathname());
        if (in_array($relativePath, $forbidden, true)) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if (false === $contents) {
            $errors[] = 'Unable to read PHP file: '.$relativePath;
            continue;
        }

        foreach ($legacyRegexes as $regex) {
            if (1 === preg_match($regex, $contents)) {
                $errors[] = 'Legacy reference matching `'.$regex.'` found in '.$relativePath;
            }
        }
    }
}

if ([] !== $errors) {
    echo "Paying value-object/exception name-form report: FAIL\n";
    foreach ($errors as $error) {
        echo ' - '.$error."\n";
    }
    exit(1);
}

echo "Paying value-object/exception name-form report: OK\n";
