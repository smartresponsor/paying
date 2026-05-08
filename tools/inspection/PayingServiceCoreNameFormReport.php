<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$expectedPresent = [
    'src/Service/PaymentCircuitBreaker.php' => 'App\\Paying\\Service\\PaymentCircuitBreaker',
    'src/Service/PaymentMetric.php' => 'App\\Paying\\Service\\PaymentMetric',
    'src/Service/PaymentProviderGuard.php' => 'App\\Paying\\Service\\PaymentProviderGuard',
    'src/Service/PaymentProviderRouter.php' => 'App\\Paying\\Service\\PaymentProviderRouter',
    'src/Service/PaymentRetryExecutor.php' => 'App\\Paying\\Service\\PaymentRetryExecutor',
    'src/ServiceInterface/PaymentCircuitBreakerInterface.php' => 'App\\Paying\\ServiceInterface\\PaymentCircuitBreakerInterface',
    'src/ServiceInterface/PaymentMetricInterface.php' => 'App\\Paying\\ServiceInterface\\PaymentMetricInterface',
    'src/ServiceInterface/PaymentProviderGuardInterface.php' => 'App\\Paying\\ServiceInterface\\PaymentProviderGuardInterface',
    'src/ServiceInterface/PaymentProviderRouterInterface.php' => 'App\\Paying\\ServiceInterface\\PaymentProviderRouterInterface',
    'src/ServiceInterface/PaymentRetryExecutorInterface.php' => 'App\\Paying\\ServiceInterface\\PaymentRetryExecutorInterface',
];

$expectedAbsent = [
    'src/Service/CircuitBreaker.php',
    'src/Service/Metric.php',
    'src/Service/ProviderGuard.php',
    'src/Service/ProviderRouter.php',
    'src/Service/RetryExecutor.php',
    'src/ServiceInterface/CircuitBreakerInterface.php',
    'src/ServiceInterface/MetricInterface.php',
    'src/ServiceInterface/ProviderGuardInterface.php',
    'src/ServiceInterface/ProviderRouterInterface.php',
    'src/ServiceInterface/RetryExecutorInterface.php',
];

$failures = [];

foreach ($expectedPresent as $relative => $fqcn) {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        $failures[] = 'Missing canonical core service file: '.$relative;
        continue;
    }

    $contents = file_get_contents($path);
    if ($contents === false || !str_contains($contents, basename(str_replace('\\', '/', $fqcn), '.php'))) {
        $failures[] = 'Canonical file does not expose expected symbol: '.$relative.' -> '.$fqcn;
    }
}

foreach ($expectedAbsent as $relative) {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (is_file($path)) {
        $failures[] = 'Legacy unprefixed core service file still present: '.$relative;
    }
}

$scanRoots = ['src', 'tests', 'config'];
$legacySymbols = [
    'CircuitBreakerInterface',
    'MetricInterface',
    'ProviderGuardInterface',
    'ProviderRouterInterface',
    'RetryExecutorInterface',
    'CircuitBreaker',
    'Metric',
    'ProviderGuard',
    'ProviderRouter',
    'RetryExecutor',
];

foreach ($scanRoots as $scanRoot) {
    $directory = $root.DIRECTORY_SEPARATOR.$scanRoot;
    if (!is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $extension = $file->getExtension();
        if (!in_array($extension, ['php', 'yaml', 'yml', 'xml'], true)) {
            continue;
        }

        $relative = str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname());
        if (str_contains($relative, 'PaymentCircuitBreaker')
            || str_contains($relative, 'PaymentMetric')
            || str_contains($relative, 'PaymentProviderGuard')
            || str_contains($relative, 'PaymentProviderRouter')
            || str_contains($relative, 'PaymentRetryExecutor')) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            continue;
        }

        foreach ($legacySymbols as $symbol) {
            if (preg_match('/(?<![A-Za-z0-9_])'.preg_quote($symbol, '/').'(?![A-Za-z0-9_])/', $contents) === 1) {
                $failures[] = 'Legacy core service symbol reference: '.$relative.' -> '.$symbol;
            }
        }
    }
}

if ($failures !== []) {
    fwrite(STDERR, "Paying service core name-form report: FAIL\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, ' - '.$failure."\n");
    }

    exit(1);
}

fwrite(STDOUT, "Paying service core name-form report: OK\n");
