<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$residualPaths = [
    'src/Attribute/RequireScope.php',
    'src/Controller/DlqController.php',
    'src/Controller/FinalizeController.php',
    'src/Controller/MetricController.php',
    'src/Controller/StartController.php',
    'src/Controller/StatusController.php',
    'src/Controller/WebhookController.php',
    'src/Controller/Webhook/PayPalWebhookController.php',
    'src/Controller/Webhook/StripeWebhookController.php',
    'src/ControllerInterface/DlqControllerInterface.php',
    'src/ControllerInterface/FinalizeControllerInterface.php',
    'src/ControllerInterface/MetricControllerInterface.php',
    'src/ControllerInterface/StartControllerInterface.php',
    'src/ControllerInterface/StatusControllerInterface.php',
    'src/ControllerInterface/WebhookControllerInterface.php',
    'src/Exception/OutboxOperationException.php',
    'src/Infrastructure/Console/DlqReplayCommand.php',
    'src/Infrastructure/Console/GateSloCommand.php',
    'src/Infrastructure/Console/IdemPurgeCommand.php',
    'src/Infrastructure/Console/OutboxRunCommand.php',
    'src/Infrastructure/Console/ProjectionRebuildCommand.php',
    'src/Infrastructure/Console/ProjectionSyncCommand.php',
    'src/Infrastructure/Console/ReconcileRunCommand.php',
    'src/Infrastructure/Console/SlaReportCommand.php',
    'src/Infrastructure/AuditLogger.php',
    'src/Infrastructure/DbalIdempotencyStore.php',
    'src/Infrastructure/MetricSubscriber.php',
    'src/Infrastructure/OutboxPublisher.php',
    'src/Infrastructure/OutboxWorker.php',
    'src/Infrastructure/PublisherTransportLog.php',
    'src/Infrastructure/RateLimitSubscriber.php',
    'src/Infrastructure/RedisIdempotencyStore.php',
    'src/Infrastructure/ResponseHeaderSubscriber.php',
    'src/Infrastructure/ScopeGuardSubscriber.php',
    'src/Infrastructure/PaymentMetricSubscriber.php',
    'src/Infrastructure/PaymentRateLimitSubscriber.php',
    'src/Infrastructure/PaymentResponseHeaderSubscriber.php',
    'src/InfrastructureInterface/AuditLoggerInterface.php',
    'src/InfrastructureInterface/OutboxPublisherInterface.php',
    'src/InfrastructureInterface/PublisherTransportInterface.php',
    'src/Service/ApiErrorResponseFactory.php',
    'src/Service/ApiJsonBodyDecoder.php',
    'src/Service/ApiRequestValidator.php',
    'src/Service/CircuitBreaker.php',
    'src/Service/DlqService.php',
    'src/Service/Gateway/PayPalGateway.php',
    'src/Service/Gateway/StripeGateway.php',
    'src/Service/IdempotencyService.php',
    'src/Service/IdempotencyStoreFactory.php',
    'src/Service/InternalPaymentProvider.php',
    'src/Service/Mapper/AdyenEventMapper.php',
    'src/Service/Mapper/StripeEventMapper.php',
    'src/Service/Metric.php',
    'src/Service/OidcJwksCache.php',
    'src/Service/Order/NullOrderPaymentSync.php',
    'src/Service/PayPalPaymentProvider.php',
    'src/Service/ProjectionLagService.php',
    'src/Service/ProjectionSync.php',
    'src/Service/ProviderGuard.php',
    'src/Service/ProviderRouter.php',
    'src/Service/ReconciliationService.php',
    'src/Service/RefundService.php',
    'src/Service/RetryExecutor.php',
    'src/Service/SlaReporter.php',
    'src/Service/StripePaymentProvider.php',
    'src/Service/TokenVerifier.php',
    'src/Service/ValidationErrorMapper.php',
    'src/Service/Webhook/JsonSchemaValidator.php',
    'src/Service/Webhook/PayPalEventNormalizer.php',
    'src/Service/Webhook/PayPalSignatureValidator.php',
    'src/Service/Webhook/StripeEventNormalizer.php',
    'src/Service/Webhook/StripeSignatureValidator.php',
    'src/Service/WebhookIngestService.php',
    'src/Service/WebhookVerifier.php',
    'src/ServiceInterface/ApiErrorResponseFactoryInterface.php',
    'src/ServiceInterface/ApiJsonBodyDecoderInterface.php',
    'src/ServiceInterface/ApiRequestValidatorInterface.php',
    'src/ServiceInterface/CircuitBreakerInterface.php',
    'src/ServiceInterface/DlqServiceInterface.php',
    'src/ServiceInterface/IdempotencyServiceInterface.php',
    'src/ServiceInterface/MetricInterface.php',
    'src/ServiceInterface/OidcJwksCacheInterface.php',
    'src/ServiceInterface/ProjectionLagServiceInterface.php',
    'src/ServiceInterface/ProjectionSyncInterface.php',
    'src/ServiceInterface/ProviderGuardInterface.php',
    'src/ServiceInterface/ProviderRouterInterface.php',
    'src/ServiceInterface/ReconciliationServiceInterface.php',
    'src/ServiceInterface/RefundServiceInterface.php',
    'src/ServiceInterface/RetryExecutorInterface.php',
    'src/ServiceInterface/SlaReporterInterface.php',
    'src/ServiceInterface/TokenVerifierInterface.php',
    'src/ServiceInterface/ValidationErrorMapperInterface.php',
    'src/ServiceInterface/WebhookIngestServiceInterface.php',
    'src/ServiceInterface/WebhookVerifierInterface.php',
    'src/ValueObject/GatewayCode.php',
    'src/ValueObject/Money.php',
    'src/ValueObject/TransactionId.php',
    'tests/Unit/ApiErrorResponseFactoryTest.php',
    'tests/Unit/ApiJsonBodyDecoderTest.php',
    'tests/Unit/ApiRequestValidatorTest.php',
    'tests/Unit/FinalizeControllerTest.php',
    'tests/Unit/OutboxPublisherEnqueueTest.php',
    'tests/Unit/OutboxWorkerRetryTest.php',
    'tests/Unit/PayPalEventNormalizerTest.php',
    'tests/Unit/ProjectionLagServiceTest.php',
    'tests/Unit/ProviderGuardTest.php',
    'tests/Unit/RefundServiceTest.php',
    'tests/Unit/ResponseHeaderSubscriberTest.php',
    'tests/Unit/RetryExecutorTest.php',
    'tests/Unit/ScopeGuardSubscriberTest.php',
    'tests/Unit/StripeEventNormalizerTest.php',
    'tests/Unit/TokenVerifierTest.php',
    'tests/Unit/ValidationErrorMapperTest.php',
    'tests/Unit/ValueObject/MoneyTest.php',
];

$existing = [];
foreach ($residualPaths as $relativePath) {
    $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($candidate)) {
        $existing[] = $relativePath;
    }
}

echo "Paying post-subscriber residual retirement report\n";
echo "Tracked residual files: " . count($residualPaths) . "\n";

if ([] !== $existing) {
    echo "Status: FAIL\n";
    foreach ($existing as $path) {
        echo " - residual legacy file still exists: " . $path . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
exit(0);
