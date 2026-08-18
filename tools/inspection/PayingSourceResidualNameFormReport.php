<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];
$warnings = [];

$coveredLegacyFiles = [
    'src/Attribute/RequireScope.php' => 'src/Attribute/PaymentRequireScopeAttribute.php',
    'src/Controller/DlqController.php' => 'src/Controller/PaymentDlqController.php',
    'src/Controller/FinalizeController.php' => 'src/Controller/PaymentFinalizeController.php',
    'src/Controller/MetricController.php' => 'src/Controller/PaymentMetricController.php',
    'src/Controller/StartController.php' => 'src/Controller/PaymentStartController.php',
    'src/Controller/StatusController.php' => 'src/Controller/PaymentStatusController.php',
    'src/Controller/WebhookController.php' => 'src/Controller/PaymentWebhookController.php',
    'src/Controller/Webhook/PayPalWebhookController.php' => 'src/Controller/Webhook/PaymentPayPalWebhookController.php',
    'src/Controller/Webhook/StripeWebhookController.php' => 'src/Controller/Webhook/PaymentStripeWebhookController.php',
    'src/ControllerInterface/DlqControllerInterface.php' => 'src/ControllerInterface/PaymentDlqControllerInterface.php',
    'src/ControllerInterface/FinalizeControllerInterface.php' => 'src/ControllerInterface/PaymentFinalizeControllerInterface.php',
    'src/ControllerInterface/MetricControllerInterface.php' => 'src/ControllerInterface/PaymentMetricControllerInterface.php',
    'src/ControllerInterface/StartControllerInterface.php' => 'src/ControllerInterface/PaymentStartControllerInterface.php',
    'src/ControllerInterface/StatusControllerInterface.php' => 'src/ControllerInterface/PaymentStatusControllerInterface.php',
    'src/ControllerInterface/WebhookControllerInterface.php' => 'src/ControllerInterface/PaymentWebhookControllerInterface.php',
    'src/Exception/OutboxOperationException.php' => 'src/Exception/PaymentOutboxOperationException.php',
    'src/Infrastructure/AuditLogger.php' => 'src/Infrastructure/PaymentAuditLogger.php',
    'src/Infrastructure/DbalIdempotencyStore.php' => 'src/Infrastructure/PaymentDbalIdempotencyStore.php',
    'src/Infrastructure/MetricSubscriber.php' => 'src/Subscriber/PaymentMetricSubscriber.php',
    'src/Infrastructure/OutboxPublisher.php' => 'src/Infrastructure/PaymentOutboxPublisher.php',
    'src/Infrastructure/OutboxWorker.php' => 'src/Infrastructure/PaymentOutboxWorker.php',
    'src/Infrastructure/PublisherTransportLog.php' => 'src/Infrastructure/PaymentPublisherTransportLog.php',
    'src/Infrastructure/RateLimitSubscriber.php' => 'src/Subscriber/PaymentRateLimitSubscriber.php',
    'src/Infrastructure/RedisIdempotencyStore.php' => 'src/Infrastructure/PaymentRedisIdempotencyStore.php',
    'src/Infrastructure/ResponseHeaderSubscriber.php' => 'src/Subscriber/PaymentResponseHeaderSubscriber.php',
    'src/Infrastructure/ScopeGuardSubscriber.php' => 'src/Subscriber/PaymentScopeGuardSubscriber.php',
    'src/Infrastructure/Console/DlqReplayCommand.php' => 'src/Infrastructure/Console/PaymentDlqReplayCommand.php',
    'src/Infrastructure/Console/GateSloCommand.php' => 'src/Infrastructure/Console/PaymentGateSloCommand.php',
    'src/Infrastructure/Console/IdemPurgeCommand.php' => 'src/Infrastructure/Console/PaymentIdemPurgeCommand.php',
    'src/Infrastructure/Console/OutboxRunCommand.php' => 'src/Infrastructure/Console/PaymentOutboxRunCommand.php',
    'src/Infrastructure/Console/ProjectionRebuildCommand.php' => 'src/Infrastructure/Console/PaymentProjectionRebuildCommand.php',
    'src/Infrastructure/Console/ProjectionSyncCommand.php' => 'src/Infrastructure/Console/PaymentProjectionSyncCommand.php',
    'src/Infrastructure/Console/ReconcileRunCommand.php' => 'src/Infrastructure/Console/PaymentReconcileRunCommand.php',
    'src/Infrastructure/Console/SlaReportCommand.php' => 'src/Infrastructure/Console/PaymentSlaReportCommand.php',
    'src/InfrastructureInterface/AuditLoggerInterface.php' => 'src/InfrastructureInterface/PaymentAuditLoggerInterface.php',
    'src/InfrastructureInterface/OutboxPublisherInterface.php' => 'src/InfrastructureInterface/PaymentOutboxPublisherInterface.php',
    'src/InfrastructureInterface/PublisherTransportInterface.php' => 'src/InfrastructureInterface/PaymentPublisherTransportInterface.php',
    'src/Service/ApiErrorResponseFactory.php' => 'src/Service/PaymentApiErrorResponseFactory.php',
    'src/Service/ApiJsonBodyDecoder.php' => 'src/Service/PaymentApiJsonBodyDecoder.php',
    'src/Service/ApiRequestValidator.php' => 'src/Service/PaymentApiRequestValidator.php',
    'src/Service/CircuitBreaker.php' => 'src/Service/PaymentCircuitBreaker.php',
    'src/Service/DlqService.php' => 'src/Service/PaymentDlqService.php',
    'src/Service/GatewayCode.php' => 'src/Service/PaymentGatewayCode.php',
    'src/Service/IdempotencyService.php' => 'src/Service/PaymentIdempotencyService.php',
    'src/Service/IdempotencyStoreFactory.php' => 'src/Service/PaymentIdempotencyStoreFactory.php',
    'src/Service/InternalPaymentProvider.php' => 'src/Service/PaymentInternalProvider.php',
    'src/Service/Metric.php' => 'src/Service/PaymentMetric.php',
    'src/Service/Money.php' => 'src/Service/PaymentMoney.php',
    'src/Service/OidcJwksCache.php' => 'src/Service/PaymentOidcJwksCache.php',
    'src/Service/PayPalPaymentProvider.php' => 'src/Service/PaymentPayPalProvider.php',
    'src/Service/ProjectionLagService.php' => 'src/Service/PaymentProjectionLagService.php',
    'src/Service/ProjectionSync.php' => 'src/Service/PaymentProjectionSyncService.php',
    'src/Service/ProviderGuard.php' => 'src/Service/PaymentProviderGuard.php',
    'src/Service/ProviderRouter.php' => 'src/Service/PaymentProviderRouter.php',
    'src/Service/ReconciliationService.php' => 'src/Service/PaymentReconciliationService.php',
    'src/Service/RefundService.php' => 'src/Service/PaymentRefundService.php',
    'src/Service/RetryExecutor.php' => 'src/Service/PaymentRetryExecutor.php',
    'src/Service/SlaReporter.php' => 'src/Service/PaymentSlaReporterService.php',
    'src/Service/StripePaymentProvider.php' => 'src/Service/PaymentStripeProvider.php',
    'src/Service/TokenVerifier.php' => 'src/Service/PaymentTokenVerifier.php',
    'src/Service/TransactionId.php' => 'src/Service/PaymentTransactionId.php',
    'src/Service/ValidationErrorMapper.php' => 'src/Service/PaymentValidationErrorMapper.php',
    'src/Service/WebhookIngestService.php' => 'src/Service/PaymentWebhookIngestService.php',
    'src/Service/WebhookVerifier.php' => 'src/Service/PaymentWebhookVerifierService.php',
    'src/Service/Gateway/PayPalGateway.php' => 'src/Service/Gateway/PaymentPayPalGateway.php',
    'src/Service/Gateway/StripeGateway.php' => 'src/Service/Gateway/PaymentStripeGateway.php',
    'src/Service/Mapper/AdyenEventMapper.php' => 'src/Service/Mapper/PaymentAdyenEventMapper.php',
    'src/Service/Mapper/StripeEventMapper.php' => 'src/Service/Mapper/PaymentStripeEventMapper.php',
    'src/Service/Order/NullOrderPaymentSync.php' => 'src/Service/Order/PaymentNullOrderPaymentSync.php',
    'src/Service/Validation/JsonSchemaValidator.php' => 'src/Service/Validation/PaymentJsonSchemaValidator.php',
    'src/Service/Webhook/PayPalEventNormalizer.php' => 'src/Service/Webhook/PaymentPayPalEventNormalizer.php',
    'src/Service/Webhook/PayPalSignatureValidator.php' => 'src/Service/Webhook/PaymentPayPalSignatureValidator.php',
    'src/Service/Webhook/StripeEventNormalizer.php' => 'src/Service/Webhook/PaymentStripeEventNormalizer.php',
    'src/Service/Webhook/StripeSignatureValidator.php' => 'src/Service/Webhook/PaymentStripeSignatureValidator.php',
];

foreach ($coveredLegacyFiles as $legacyPath => $canonicalPath) {
    $legacyExists = is_file($root . DIRECTORY_SEPARATOR . $legacyPath);
    $canonicalExists = is_file($root . DIRECTORY_SEPARATOR . $canonicalPath);

    if ($legacyExists && $canonicalExists) {
        $errors[] = 'Covered legacy source duplicate remains: ' . $legacyPath . ' -> ' . $canonicalPath;
        continue;
    }

    if ($legacyExists && !$canonicalExists) {
        $warnings[] = 'Legacy source path exists but canonical replacement is absent: ' . $legacyPath . ' -> ' . $canonicalPath;
        continue;
    }

    if (!$legacyExists && !$canonicalExists) {
        $warnings[] = 'Neither legacy nor canonical source path exists for covered pair: ' . $legacyPath . ' -> ' . $canonicalPath;
    }
}

$doublePrefixMatches = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . DIRECTORY_SEPARATOR . 'src', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
        continue;
    }

    if (str_contains($file->getFilename(), 'PaymentPayment')) {
        $doublePrefixMatches[] = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
    }
}

foreach ($doublePrefixMatches as $path) {
    $errors[] = 'Double Payment prefix drift remains: ' . $path;
}

if ($errors !== []) {
    fwrite(STDERR, "Paying source residual nameEntity-form report: FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - ' . $error . "\n");
    }

    if ($warnings !== []) {
        fwrite(STDERR, "Warnings:\n");
        foreach ($warnings as $warning) {
            fwrite(STDERR, ' - ' . $warning . "\n");
        }
    }

    exit(1);
}

fwrite(STDOUT, "Paying source residual nameEntity-form report: OK\n");
fwrite(STDOUT, 'Covered legacy source pairs: ' . count($coveredLegacyFiles) . "\n");
if ($warnings !== []) {
    fwrite(STDOUT, "Warnings:\n");
    foreach ($warnings as $warning) {
        fwrite(STDOUT, ' - ' . $warning . "\n");
    }
}
