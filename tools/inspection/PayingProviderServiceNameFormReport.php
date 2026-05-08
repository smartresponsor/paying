<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$expected = [
    'src/Service/PaymentInternalProvider.php' => 'PaymentInternalProvider',
    'src/Service/PaymentStripeProvider.php' => 'PaymentStripeProvider',
    'src/Service/PaymentPayPalProvider.php' => 'PaymentPayPalProvider',
];

$legacy = [
    'src/Service/InternalPaymentProvider.php' => 'InternalPaymentProvider',
    'src/Service/StripePaymentProvider.php' => 'StripePaymentProvider',
    'src/Service/PayPalPaymentProvider.php' => 'PayPalPaymentProvider',
];

$errors = [];
foreach ($expected as $path => $className) {
    $fullPath = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (!is_file($fullPath)) {
        $errors[] = 'Missing canonical provider file: '.$path;
        continue;
    }

    $source = (string) file_get_contents($fullPath);
    if (!str_contains($source, 'class '.$className.' implements PaymentProviderInterface')) {
        $errors[] = 'Canonical provider class signature not found: '.$className;
    }
}

$searchRoots = [
    $root.DIRECTORY_SEPARATOR.'src',
    $root.DIRECTORY_SEPARATOR.'tests',
    $root.DIRECTORY_SEPARATOR.'config',
];

foreach ($legacy as $path => $className) {
    $fullPath = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($fullPath)) {
        $errors[] = 'Legacy provider file remains: '.$path;
    }

    foreach ($searchRoots as $searchRoot) {
        if (!is_dir($searchRoot)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchRoot));
        foreach ($iterator as $candidate) {
            if (!$candidate->isFile()) {
                continue;
            }

            $extension = $candidate->getExtension();
            if (!in_array($extension, ['php', 'yaml', 'yml'], true)) {
                continue;
            }

            $source = (string) file_get_contents($candidate->getPathname());
            if (str_contains($source, $className)) {
                $relative = str_replace($root.DIRECTORY_SEPARATOR, '', $candidate->getPathname());
                $errors[] = 'Legacy provider class reference remains in '.$relative.': '.$className;
            }
        }
    }
}

if ([] !== $errors) {
    echo "Paying provider service name-form report: FAIL\n";
    foreach (array_values(array_unique($errors)) as $error) {
        echo ' - '.$error."\n";
    }
    exit(1);
}

echo "Paying provider service name-form report: OK\n";
echo "Canonical providers: PaymentInternalProvider, PaymentStripeProvider, PaymentPayPalProvider\n";
