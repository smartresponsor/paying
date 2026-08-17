<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$requiredFiles = [
    'src/Controller/Webhook/PaymentPayPalWebhookController.php' => 'final readonly class PaymentPayPalWebhookController',
    'src/Controller/Webhook/PaymentStripeWebhookController.php' => 'final readonly class PaymentStripeWebhookController',
];

$legacyFiles = [
    'src/Controller/Webhook/PayPalWebhookController.php',
    'src/Controller/Webhook/StripeWebhookController.php',
];

foreach ($requiredFiles as $relativePath => $expectedClassDeclaration) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($path)) {
        $errors[] = sprintf('Missing canonical webhook controller file: %s', $relativePath);
        continue;
    }

    $contents = (string) file_get_contents($path);
    if (!str_contains($contents, $expectedClassDeclaration)) {
        $errors[] = sprintf('Canonical webhook controller declaration mismatch in %s', $relativePath);
    }
}

foreach ($legacyFiles as $relativePath) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($path)) {
        $errors[] = sprintf('Legacy unprefixed webhook controller still exists: %s', $relativePath);
    }
}

$searchRoots = ['src', 'config', 'tests'];
$legacyReferences = [
    'App\\Paying\\Controller\\Webhook\\PayPalWebhookController',
    'App\\Paying\\Controller\\Webhook\\StripeWebhookController',
    'src/Controller/Webhook/PayPalWebhookController.php',
    'src/Controller/Webhook/StripeWebhookController.php',
];

foreach ($searchRoots as $searchRoot) {
    $directory = $root . DIRECTORY_SEPARATOR . $searchRoot;
    if (!is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $path = $file->getPathname();
        $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
        if (str_contains($relativePath, 'PayingWebhookControllerNameFormReport.php')) {
            continue;
        }

        $contents = (string) file_get_contents($path);
        foreach ($legacyReferences as $legacyReference) {
            if (str_contains($contents, $legacyReference)) {
                $errors[] = sprintf('Legacy webhook controller reference %s found in %s', $legacyReference, $relativePath);
            }
        }
    }
}

$routeFile = $root . DIRECTORY_SEPARATOR . 'config/routes/payment_webhook.yaml';
$routeContents = is_file($routeFile) ? (string) file_get_contents($routeFile) : '';
foreach ([
    'App\\Paying\\Controller\\Webhook\\PaymentPayPalWebhookController::__invoke',
    'App\\Paying\\Controller\\Webhook\\PaymentStripeWebhookController::__invoke',
] as $expectedRouteController) {
    if (!str_contains($routeContents, $expectedRouteController)) {
        $errors[] = sprintf('Webhook route is not wired to canonical controller: %s', $expectedRouteController);
    }
}

if ([] !== $errors) {
    fwrite(STDERR, "Paying webhook controller name-form report: FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - ' . $error . "\n");
    }
    exit(1);
}

fwrite(STDOUT, "Paying webhook controller name-form report: OK\n");
fwrite(STDOUT, "Canonical webhook controllers: PaymentPayPalWebhookController, PaymentStripeWebhookController\n");
