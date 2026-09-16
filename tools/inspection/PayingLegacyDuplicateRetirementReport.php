<?php

declare(strict_types=1);

/**
 * Reports residual legacy files that were superseded by Payment-prefixed classes in canonicalization waves.
 *
 * This is intentionally report-only. Removals are performed by the delivery PowerShell script with backup
 * and SHA-256 guarded retirement, because zip overlays cannot delete files safely.
 */
final class PayingLegacyDuplicateRetirementReport
{
    /** @var array<string, string> */
    private const RETIRED_TO_CANONICAL = [
        'src/Controller/StartController.php' => 'src/Controller/PaymentStartController.php',
        'src/Controller/FinalizeController.php' => 'src/Controller/PaymentFinalizeController.php',
        'src/Controller/StatusController.php' => 'src/Controller/PaymentStatusController.php',
        'src/Controller/WebhookController.php' => 'src/Controller/PaymentWebhookController.php',
        'src/Controller/MetricController.php' => 'src/Controller/PaymentMetricController.php',
        'src/Controller/DlqController.php' => 'src/Controller/PaymentDlqController.php',
        'src/ControllerInterface/StartControllerInterface.php' => 'src/ControllerInterface/PaymentStartControllerInterface.php',
        'src/ControllerInterface/FinalizeControllerInterface.php' => 'src/ControllerInterface/PaymentFinalizeControllerInterface.php',
        'src/ControllerInterface/StatusControllerInterface.php' => 'src/ControllerInterface/PaymentStatusControllerInterface.php',
        'src/ControllerInterface/WebhookControllerInterface.php' => 'src/ControllerInterface/PaymentWebhookControllerInterface.php',
        'src/ControllerInterface/MetricControllerInterface.php' => 'src/ControllerInterface/PaymentMetricControllerInterface.php',
        'src/ControllerInterface/DlqControllerInterface.php' => 'src/ControllerInterface/PaymentDlqControllerInterface.php',
        'src/Service/CircuitBreaker.php' => 'src/Service/PaymentCircuitBreaker.php',
        'src/Service/Metric.php' => 'src/Service/PaymentMetric.php',
        'src/Service/ProviderGuard.php' => 'src/Service/PaymentProviderGuard.php',
        'src/Service/ProviderRouter.php' => 'src/Service/PaymentProviderRouter.php',
        'src/Service/RetryExecutor.php' => 'src/Service/PaymentRetryExecutor.php',
        'src/ServiceInterface/CircuitBreakerInterface.php' => 'src/ServiceInterface/PaymentCircuitBreakerInterface.php',
        'src/ServiceInterface/MetricInterface.php' => 'src/ServiceInterface/PaymentMetricInterface.php',
        'src/ServiceInterface/ProviderGuardInterface.php' => 'src/ServiceInterface/PaymentProviderGuardInterface.php',
        'src/ServiceInterface/ProviderRouterInterface.php' => 'src/ServiceInterface/PaymentProviderRouterInterface.php',
        'src/ServiceInterface/RetryExecutorInterface.php' => 'src/ServiceInterface/PaymentRetryExecutorInterface.php',
        'src/Service/ApiErrorResponseFactory.php' => 'src/Service/PaymentApiErrorResponseFactory.php',
        'src/Service/ApiJsonBodyDecoder.php' => 'src/Service/PaymentApiJsonBodyDecoder.php',
        'src/Service/ApiRequestValidator.php' => 'src/Service/PaymentApiRequestValidator.php',
        'src/Service/ValidationErrorMapper.php' => 'src/Service/PaymentValidationErrorMapper.php',
        'src/Service/OidcJwksCache.php' => 'src/Service/PaymentOidcJwksCache.php',
        'src/Service/TokenVerifier.php' => 'src/Service/PaymentTokenVerifier.php',
        'src/ServiceInterface/ApiErrorResponseFactoryInterface.php' => 'src/ServiceInterface/PaymentApiErrorResponseFactoryInterface.php',
        'src/ServiceInterface/ApiJsonBodyDecoderInterface.php' => 'src/ServiceInterface/PaymentApiJsonBodyDecoderInterface.php',
        'src/ServiceInterface/ApiRequestValidatorInterface.php' => 'src/ServiceInterface/PaymentApiRequestValidatorInterface.php',
        'src/ServiceInterface/ValidationErrorMapperInterface.php' => 'src/ServiceInterface/PaymentValidationErrorMapperInterface.php',
        'src/ServiceInterface/OidcJwksCacheInterface.php' => 'src/ServiceInterface/PaymentOidcJwksCacheInterface.php',
        'src/ServiceInterface/TokenVerifierInterface.php' => 'src/ServiceInterface/PaymentTokenVerifierInterface.php',
        'src/Infrastructure/Console/DlqReplayCommand.php' => 'src/Infrastructure/Console/PaymentDlqReplayCommand.php',
        'src/Infrastructure/Console/GateSloCommand.php' => 'src/Infrastructure/Console/PaymentGateSloCommand.php',
        'src/Infrastructure/Console/IdemPurgeCommand.php' => 'src/Infrastructure/Console/PaymentIdemPurgeCommand.php',
        'src/Infrastructure/Console/OutboxRunCommand.php' => 'src/Infrastructure/Console/PaymentOutboxRunCommand.php',
        'src/Infrastructure/Console/ProjectionRebuildCommand.php' => 'src/Infrastructure/Console/PaymentProjectionRebuildCommand.php',
        'src/Infrastructure/Console/ProjectionSyncCommand.php' => 'src/Infrastructure/Console/PaymentProjectionSyncCommand.php',
        'src/Infrastructure/Console/ReconcileRunCommand.php' => 'src/Infrastructure/Console/PaymentReconcileRunCommand.php',
        'src/Infrastructure/Console/SlaReportCommand.php' => 'src/Infrastructure/Console/PaymentSlaReportCommand.php',
        'src/Infrastructure/AuditLogger.php' => 'src/Infrastructure/PaymentAuditLogger.php',
        'src/Infrastructure/OutboxPublisher.php' => 'src/Infrastructure/PaymentOutboxPublisher.php',
        'src/Infrastructure/OutboxWorker.php' => 'src/Infrastructure/PaymentOutboxWorker.php',
        'src/Infrastructure/DbalIdempotencyStore.php' => 'src/Infrastructure/PaymentDbalIdempotencyStore.php',
        'src/Infrastructure/RedisIdempotencyStore.php' => 'src/Infrastructure/PaymentRedisIdempotencyStore.php',
        'src/Infrastructure/MetricSubscriber.php' => 'src/Infrastructure/PaymentMetricSubscriber.php',
        'src/Infrastructure/RateLimitSubscriber.php' => 'src/Infrastructure/PaymentRateLimitSubscriber.php',
        'src/Infrastructure/ResponseHeaderSubscriber.php' => 'src/Infrastructure/PaymentResponseHeaderSubscriber.php',
        'src/Infrastructure/PublisherTransportLog.php' => 'src/Infrastructure/PaymentPublisherTransportLog.php',
        'src/InfrastructureInterface/AuditLoggerInterface.php' => 'src/InfrastructureInterface/PaymentAuditLoggerInterface.php',
        'src/InfrastructureInterface/OutboxPublisherInterface.php' => 'src/InfrastructureInterface/PaymentOutboxPublisherInterface.php',
        'src/InfrastructureInterface/PublisherTransportInterface.php' => 'src/InfrastructureInterface/PaymentPublisherTransportInterface.php',
        'src/Service/DlqService.php' => 'src/Service/PaymentDlqService.php',
        'src/Service/IdempotencyService.php' => 'src/Service/PaymentIdempotencyService.php',
        'src/Service/IdempotencyStoreFactory.php' => 'src/Service/PaymentIdempotencyStoreFactory.php',
        'src/Service/ProjectionLagService.php' => 'src/Service/PaymentProjectionLagService.php',
        'src/Service/ProjectionSync.php' => 'src/Service/PaymentProjectionSyncService.php',
        'src/Service/ReconciliationService.php' => 'src/Service/PaymentReconciliationService.php',
        'src/Service/RefundService.php' => 'src/Service/PaymentRefundService.php',
        'src/Service/SlaReporter.php' => 'src/Service/PaymentSlaReporterService.php',
        'src/Service/WebhookIngestService.php' => 'src/Service/PaymentWebhookIngestService.php',
        'src/Service/WebhookVerifier.php' => 'src/Service/PaymentWebhookVerifierService.php',
        'src/ServiceInterface/DlqServiceInterface.php' => 'src/ServiceInterface/PaymentDlqServiceInterface.php',
        'src/ServiceInterface/IdempotencyServiceInterface.php' => 'src/ServiceInterface/PaymentIdempotencyServiceInterface.php',
        'src/ServiceInterface/ProjectionLagServiceInterface.php' => 'src/ServiceInterface/PaymentProjectionLagServiceInterface.php',
        'src/ServiceInterface/ProjectionSyncInterface.php' => 'src/ServiceInterface/PaymentProjectionSyncServiceInterface.php',
        'src/ServiceInterface/ReconciliationServiceInterface.php' => 'src/ServiceInterface/PaymentReconciliationServiceInterface.php',
        'src/ServiceInterface/RefundServiceInterface.php' => 'src/ServiceInterface/PaymentRefundServiceInterface.php',
        'src/ServiceInterface/SlaReporterInterface.php' => 'src/ServiceInterface/PaymentSlaReporterServiceInterface.php',
        'src/ServiceInterface/WebhookIngestServiceInterface.php' => 'src/ServiceInterface/PaymentWebhookIngestServiceInterface.php',
        'src/ServiceInterface/WebhookVerifierInterface.php' => 'src/ServiceInterface/PaymentWebhookVerifierServiceInterface.php',
        'src/Service/Gateway/PayPalGateway.php' => 'src/Service/Gateway/PaymentPayPalGateway.php',
        'src/Service/Gateway/StripeGateway.php' => 'src/Service/Gateway/PaymentStripeGateway.php',
        'src/Service/Mapper/AdyenEventMapper.php' => 'src/Service/Mapper/PaymentAdyenEventMapper.php',
        'src/Service/Mapper/StripeEventMapper.php' => 'src/Service/Mapper/PaymentStripeEventMapper.php',
        'src/Service/Order/NullOrderPaymentSync.php' => 'src/Service/Order/PaymentNullOrderPaymentSync.php',
        'src/Service/Webhook/JsonSchemaValidator.php' => 'src/Service/Webhook/PaymentJsonSchemaValidator.php',
        'src/Service/Webhook/PayPalEventNormalizer.php' => 'src/Service/Webhook/PaymentPayPalEventNormalizer.php',
        'src/Service/Webhook/StripeEventNormalizer.php' => 'src/Service/Webhook/PaymentStripeEventNormalizer.php',
        'src/Service/Webhook/PayPalSignatureValidator.php' => 'src/Service/Webhook/PaymentPayPalSignatureValidator.php',
        'src/Service/Webhook/StripeSignatureValidator.php' => 'src/Service/Webhook/PaymentStripeSignatureValidator.php',
    ];

    public function run(string $root): int
    {
        $violations = [];

        foreach (self::RETIRED_TO_CANONICAL as $retired => $canonical) {
            $retiredPath = $root . '/' . $retired;
            $canonicalPath = $root . '/' . $canonical;

            if (is_file($retiredPath)) {
                $violations[] = sprintf('%s remains next to canonical %s', $retired, $canonical);
            }

            if (!is_file($canonicalPath)) {
                $violations[] = sprintf('%s is missing for retired legacy path %s', $canonical, $retired);
            }
        }

        if ($violations === []) {
            echo "Paying legacy duplicate retirement report: OK\n";
            return 0;
        }

        echo "Paying legacy duplicate retirement report: FAILED\n";
        foreach ($violations as $violation) {
            echo ' - ' . $violation . "\n";
        }

        return 1;
    }
}

$root = dirname(__DIR__, 2);
exit((new PayingLegacyDuplicateRetirementReport())->run($root));
