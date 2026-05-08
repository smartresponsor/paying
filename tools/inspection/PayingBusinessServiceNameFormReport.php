<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$violations = [];

$legacyFiles = [
    'src/Service/DlqService.php',
    'src/Service/IdempotencyService.php',
    'src/Service/IdempotencyStoreFactory.php',
    'src/Service/ProjectionLagService.php',
    'src/Service/ProjectionSync.php',
    'src/Service/ReconciliationService.php',
    'src/Service/RefundService.php',
    'src/Service/SlaReporter.php',
    'src/Service/WebhookIngestService.php',
    'src/Service/WebhookVerifier.php',
    'src/ServiceInterface/DlqServiceInterface.php',
    'src/ServiceInterface/IdempotencyServiceInterface.php',
    'src/ServiceInterface/ProjectionLagServiceInterface.php',
    'src/ServiceInterface/ProjectionSyncInterface.php',
    'src/ServiceInterface/ReconciliationServiceInterface.php',
    'src/ServiceInterface/RefundServiceInterface.php',
    'src/ServiceInterface/SlaReporterInterface.php',
    'src/ServiceInterface/WebhookIngestServiceInterface.php',
    'src/ServiceInterface/WebhookVerifierInterface.php',
];

foreach ($legacyFiles as $relativePath) {
    if (is_file($root . DIRECTORY_SEPARATOR . $relativePath)) {
        $violations[] = sprintf('Legacy unprefixed business service file remains: %s', $relativePath);
    }
}

$requiredFiles = [
    'src/Service/PaymentDlqService.php',
    'src/Service/PaymentIdempotencyService.php',
    'src/Service/PaymentIdempotencyStoreFactory.php',
    'src/Service/PaymentProjectionLagService.php',
    'src/Service/PaymentProjectionSyncService.php',
    'src/Service/PaymentReconciliationService.php',
    'src/Service/PaymentRefundService.php',
    'src/Service/PaymentSlaReporterService.php',
    'src/Service/PaymentWebhookIngestService.php',
    'src/Service/PaymentWebhookVerifierService.php',
    'src/ServiceInterface/PaymentDlqServiceInterface.php',
    'src/ServiceInterface/PaymentIdempotencyServiceInterface.php',
    'src/ServiceInterface/PaymentProjectionLagServiceInterface.php',
    'src/ServiceInterface/PaymentProjectionSyncServiceInterface.php',
    'src/ServiceInterface/PaymentReconciliationServiceInterface.php',
    'src/ServiceInterface/PaymentRefundServiceInterface.php',
    'src/ServiceInterface/PaymentSlaReporterServiceInterface.php',
    'src/ServiceInterface/PaymentWebhookIngestServiceInterface.php',
    'src/ServiceInterface/PaymentWebhookVerifierServiceInterface.php',
];

foreach ($requiredFiles as $relativePath) {
    if (!is_file($root . DIRECTORY_SEPARATOR . $relativePath)) {
        $violations[] = sprintf('Expected canonical business service file is missing: %s', $relativePath);
    }
}

$scanRoots = ['src', 'tests', 'config'];
foreach ($scanRoots as $scanRoot) {
    $directory = $root . DIRECTORY_SEPARATOR . $scanRoot;
    if (!is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        if (!in_array($file->getExtension(), ['php', 'yaml', 'yml', 'json', 'xml'], true)) {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());
        if (preg_match('/PaymentPayment|ServiceService/', $contents)) {
            $violations[] = sprintf('Duplicate name-form drift found in %s', substr($file->getPathname(), strlen($root) + 1));
        }
    }
}

if ([] !== $violations) {
    fwrite(STDERR, "Paying business service name-form report failed:\n- " . implode("\n- ", $violations) . "\n");
    exit(1);
}

fwrite(STDOUT, "Paying business service name-form report: OK\n");
