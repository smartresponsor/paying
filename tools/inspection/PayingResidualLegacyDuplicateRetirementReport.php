<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$residualDuplicates = [
            ['legacy' => 'src/Controller/DlqController.php', 'canonical' => 'src/Controller/PaymentDlqController.php'],
            ['legacy' => 'src/Controller/FinalizeController.php', 'canonical' => 'src/Controller/PaymentFinalizeController.php'],
            ['legacy' => 'src/Controller/MetricController.php', 'canonical' => 'src/Controller/PaymentMetricController.php'],
            ['legacy' => 'src/Controller/StartController.php', 'canonical' => 'src/Controller/PaymentStartController.php'],
            ['legacy' => 'src/Controller/StatusController.php', 'canonical' => 'src/Controller/PaymentStatusController.php'],
            ['legacy' => 'src/Controller/WebhookController.php', 'canonical' => 'src/Controller/PaymentWebhookController.php'],
            ['legacy' => 'src/ControllerInterface/DlqControllerInterface.php', 'canonical' => 'src/ControllerInterface/PaymentDlqControllerInterface.php'],
            ['legacy' => 'src/ControllerInterface/FinalizeControllerInterface.php', 'canonical' => 'src/ControllerInterface/PaymentFinalizeControllerInterface.php'],
            ['legacy' => 'src/ControllerInterface/MetricControllerInterface.php', 'canonical' => 'src/ControllerInterface/PaymentMetricControllerInterface.php'],
            ['legacy' => 'src/ControllerInterface/StartControllerInterface.php', 'canonical' => 'src/ControllerInterface/PaymentStartControllerInterface.php'],
            ['legacy' => 'src/ControllerInterface/StatusControllerInterface.php', 'canonical' => 'src/ControllerInterface/PaymentStatusControllerInterface.php'],
            ['legacy' => 'src/ControllerInterface/WebhookControllerInterface.php', 'canonical' => 'src/ControllerInterface/PaymentWebhookControllerInterface.php'],
            ['legacy' => 'src/Exception/OutboxOperationException.php', 'canonical' => 'src/Exception/PaymentOutboxOperationException.php'],
            ['legacy' => 'src/Infrastructure/AuditLogger.php', 'canonical' => 'src/Infrastructure/PaymentAuditLogger.php'],
            ['legacy' => 'src/Infrastructure/Console/DlqReplayCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentDlqReplayCommand.php'],
            ['legacy' => 'src/Infrastructure/Console/GateSloCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentGateSloCommand.php'],
            ['legacy' => 'src/Infrastructure/Console/IdemPurgeCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentIdemPurgeCommand.php'],
            ['legacy' => 'src/Infrastructure/Console/OutboxRunCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentOutboxRunCommand.php'],
            ['legacy' => 'src/Infrastructure/Console/ProjectionRebuildCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentProjectionRebuildCommand.php'],
            ['legacy' => 'src/Infrastructure/Console/ProjectionSyncCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentProjectionSyncCommand.php'],
            ['legacy' => 'src/Infrastructure/Console/ReconcileRunCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentReconcileRunCommand.php'],
            ['legacy' => 'src/Infrastructure/Console/SlaReportCommand.php', 'canonical' => 'src/Infrastructure/Console/PaymentSlaReportCommand.php'],
            ['legacy' => 'src/Infrastructure/DbalIdempotencyStore.php', 'canonical' => 'src/Infrastructure/PaymentDbalIdempotencyStore.php'],
            ['legacy' => 'src/Infrastructure/MetricSubscriber.php', 'canonical' => 'src/Infrastructure/PaymentMetricSubscriber.php'],
            ['legacy' => 'src/Infrastructure/OutboxPublisher.php', 'canonical' => 'src/Infrastructure/PaymentOutboxPublisher.php'],
            ['legacy' => 'src/Infrastructure/OutboxWorker.php', 'canonical' => 'src/Infrastructure/PaymentOutboxWorker.php'],
            ['legacy' => 'src/Infrastructure/PublisherTransportLog.php', 'canonical' => 'src/Infrastructure/PaymentPublisherTransportLog.php'],
            ['legacy' => 'src/Infrastructure/RateLimitSubscriber.php', 'canonical' => 'src/Infrastructure/PaymentRateLimitSubscriber.php'],
            ['legacy' => 'src/Infrastructure/RedisIdempotencyStore.php', 'canonical' => 'src/Infrastructure/PaymentRedisIdempotencyStore.php'],
            ['legacy' => 'src/Infrastructure/ResponseHeaderSubscriber.php', 'canonical' => 'src/Infrastructure/PaymentResponseHeaderSubscriber.php'],
            ['legacy' => 'src/InfrastructureInterface/AuditLoggerInterface.php', 'canonical' => 'src/InfrastructureInterface/PaymentAuditLoggerInterface.php'],
            ['legacy' => 'src/InfrastructureInterface/OutboxPublisherInterface.php', 'canonical' => 'src/InfrastructureInterface/PaymentOutboxPublisherInterface.php'],
            ['legacy' => 'src/InfrastructureInterface/PublisherTransportInterface.php', 'canonical' => 'src/InfrastructureInterface/PaymentPublisherTransportInterface.php'],
            ['legacy' => 'src/Service/ApiErrorResponseFactory.php', 'canonical' => 'src/Service/PaymentApiErrorResponseFactory.php'],
            ['legacy' => 'src/Service/ApiJsonBodyDecoder.php', 'canonical' => 'src/Service/PaymentApiJsonBodyDecoder.php'],
            ['legacy' => 'src/Service/ApiRequestValidator.php', 'canonical' => 'src/Service/PaymentApiRequestValidator.php'],
            ['legacy' => 'src/Service/CircuitBreaker.php', 'canonical' => 'src/Service/PaymentCircuitBreaker.php'],
            ['legacy' => 'src/Service/DlqService.php', 'canonical' => 'src/Service/PaymentDlqService.php'],
            ['legacy' => 'src/Service/Gateway/PayPalGateway.php', 'canonical' => 'src/Service/Gateway/PaymentPayPalGateway.php'],
            ['legacy' => 'src/Service/Gateway/StripeGateway.php', 'canonical' => 'src/Service/Gateway/PaymentStripeGateway.php'],
            ['legacy' => 'src/Service/IdempotencyService.php', 'canonical' => 'src/Service/PaymentIdempotencyService.php'],
            ['legacy' => 'src/Service/IdempotencyStoreFactory.php', 'canonical' => 'src/Service/PaymentIdempotencyStoreFactory.php'],
            ['legacy' => 'src/Service/Mapper/AdyenEventMapper.php', 'canonical' => 'src/Service/Mapper/PaymentAdyenEventMapper.php'],
            ['legacy' => 'src/Service/Mapper/StripeEventMapper.php', 'canonical' => 'src/Service/Mapper/PaymentStripeEventMapper.php'],
            ['legacy' => 'src/Service/Metric.php', 'canonical' => 'src/Service/PaymentMetric.php'],
            ['legacy' => 'src/Service/OidcJwksCache.php', 'canonical' => 'src/Service/PaymentOidcJwksCache.php'],
            ['legacy' => 'src/Service/Order/NullOrderPaymentSync.php', 'canonical' => 'src/Service/Order/PaymentNullOrderPaymentSync.php'],
            ['legacy' => 'src/Service/ProjectionLagService.php', 'canonical' => 'src/Service/PaymentProjectionLagService.php'],
            ['legacy' => 'src/Service/ProjectionSync.php', 'canonical' => 'src/Service/PaymentProjectionSyncService.php'],
            ['legacy' => 'src/Service/ProviderGuard.php', 'canonical' => 'src/Service/PaymentProviderGuard.php'],
            ['legacy' => 'src/Service/ProviderRouter.php', 'canonical' => 'src/Service/PaymentProviderRouter.php'],
            ['legacy' => 'src/Service/ReconciliationService.php', 'canonical' => 'src/Service/PaymentReconciliationService.php'],
            ['legacy' => 'src/Service/RefundService.php', 'canonical' => 'src/Service/PaymentRefundService.php'],
            ['legacy' => 'src/Service/RetryExecutor.php', 'canonical' => 'src/Service/PaymentRetryExecutor.php'],
            ['legacy' => 'src/Service/SlaReporter.php', 'canonical' => 'src/Service/PaymentSlaReporterService.php'],
            ['legacy' => 'src/Service/TokenVerifier.php', 'canonical' => 'src/Service/PaymentTokenVerifier.php'],
            ['legacy' => 'src/Service/ValidationErrorMapper.php', 'canonical' => 'src/Service/PaymentValidationErrorMapper.php'],
            ['legacy' => 'src/Service/Webhook/JsonSchemaValidator.php', 'canonical' => 'src/Service/Webhook/PaymentJsonSchemaValidator.php'],
            ['legacy' => 'src/Service/Webhook/PayPalEventNormalizer.php', 'canonical' => 'src/Service/Webhook/PaymentPayPalEventNormalizer.php'],
            ['legacy' => 'src/Service/Webhook/PayPalSignatureValidator.php', 'canonical' => 'src/Service/Webhook/PaymentPayPalSignatureValidator.php'],
            ['legacy' => 'src/Service/Webhook/StripeEventNormalizer.php', 'canonical' => 'src/Service/Webhook/PaymentStripeEventNormalizer.php'],
            ['legacy' => 'src/Service/Webhook/StripeSignatureValidator.php', 'canonical' => 'src/Service/Webhook/PaymentStripeSignatureValidator.php'],
            ['legacy' => 'src/Service/WebhookIngestService.php', 'canonical' => 'src/Service/PaymentWebhookIngestService.php'],
            ['legacy' => 'src/Service/WebhookVerifier.php', 'canonical' => 'src/Service/PaymentWebhookVerifierService.php'],
            ['legacy' => 'src/ServiceInterface/ApiErrorResponseFactoryInterface.php', 'canonical' => 'src/ServiceInterface/PaymentApiErrorResponseFactoryInterface.php'],
            ['legacy' => 'src/ServiceInterface/ApiJsonBodyDecoderInterface.php', 'canonical' => 'src/ServiceInterface/PaymentApiJsonBodyDecoderInterface.php'],
            ['legacy' => 'src/ServiceInterface/ApiRequestValidatorInterface.php', 'canonical' => 'src/ServiceInterface/PaymentApiRequestValidatorInterface.php'],
            ['legacy' => 'src/ServiceInterface/CircuitBreakerInterface.php', 'canonical' => 'src/ServiceInterface/PaymentCircuitBreakerInterface.php'],
            ['legacy' => 'src/ServiceInterface/DlqServiceInterface.php', 'canonical' => 'src/ServiceInterface/PaymentDlqServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/IdempotencyServiceInterface.php', 'canonical' => 'src/ServiceInterface/PaymentIdempotencyServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/MetricInterface.php', 'canonical' => 'src/ServiceInterface/PaymentMetricInterface.php'],
            ['legacy' => 'src/ServiceInterface/OidcJwksCacheInterface.php', 'canonical' => 'src/ServiceInterface/PaymentOidcJwksCacheInterface.php'],
            ['legacy' => 'src/ServiceInterface/ProjectionLagServiceInterface.php', 'canonical' => 'src/ServiceInterface/PaymentProjectionLagServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/ProjectionSyncInterface.php', 'canonical' => 'src/ServiceInterface/PaymentProjectionSyncServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/ProviderGuardInterface.php', 'canonical' => 'src/ServiceInterface/PaymentProviderGuardInterface.php'],
            ['legacy' => 'src/ServiceInterface/ProviderRouterInterface.php', 'canonical' => 'src/ServiceInterface/PaymentProviderRouterInterface.php'],
            ['legacy' => 'src/ServiceInterface/ReconciliationServiceInterface.php', 'canonical' => 'src/ServiceInterface/PaymentReconciliationServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/RefundServiceInterface.php', 'canonical' => 'src/ServiceInterface/PaymentRefundServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/RetryExecutorInterface.php', 'canonical' => 'src/ServiceInterface/PaymentRetryExecutorInterface.php'],
            ['legacy' => 'src/ServiceInterface/SlaReporterInterface.php', 'canonical' => 'src/ServiceInterface/PaymentSlaReporterServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/TokenVerifierInterface.php', 'canonical' => 'src/ServiceInterface/PaymentTokenVerifierInterface.php'],
            ['legacy' => 'src/ServiceInterface/ValidationErrorMapperInterface.php', 'canonical' => 'src/ServiceInterface/PaymentValidationErrorMapperInterface.php'],
            ['legacy' => 'src/ServiceInterface/WebhookIngestServiceInterface.php', 'canonical' => 'src/ServiceInterface/PaymentWebhookIngestServiceInterface.php'],
            ['legacy' => 'src/ServiceInterface/WebhookVerifierInterface.php', 'canonical' => 'src/ServiceInterface/PaymentWebhookVerifierServiceInterface.php'],
            ['legacy' => 'src/ValueObject/GatewayCode.php', 'canonical' => 'src/ValueObject/PaymentGatewayCode.php'],
            ['legacy' => 'src/ValueObject/Money.php', 'canonical' => 'src/ValueObject/PaymentMoney.php'],
            ['legacy' => 'src/ValueObject/TransactionId.php', 'canonical' => 'src/ValueObject/PaymentTransactionId.php'],
];

$remaining = [];
$missingCanonical = [];

foreach ($residualDuplicates as $item) {
    $legacyPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item['legacy']);
    $canonicalPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item['canonical']);

    if (is_file($legacyPath)) {
        $remaining[] = $item['legacy'];
    }

    if (!is_file($canonicalPath)) {
        $missingCanonical[] = $item['canonical'];
    }
}

echo "Paying residual legacy duplicate retirement report" . PHP_EOL;
echo "Tracked duplicate files: " . count($residualDuplicates) . PHP_EOL;
echo "Remaining legacy duplicates: " . count($remaining) . PHP_EOL;
echo "Missing canonical replacements: " . count(array_unique($missingCanonical)) . PHP_EOL;

if ($remaining !== []) {
    echo PHP_EOL . "Remaining legacy duplicate files:" . PHP_EOL;
    foreach ($remaining as $path) {
        echo " - " . $path . PHP_EOL;
    }
}

if ($missingCanonical !== []) {
    echo PHP_EOL . "Missing canonical replacement files:" . PHP_EOL;
    foreach (array_unique($missingCanonical) as $path) {
        echo " - " . $path . PHP_EOL;
    }
}

if ($remaining !== [] || $missingCanonical !== []) {
    echo PHP_EOL . "Status: FAIL" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Status: OK" . PHP_EOL;
