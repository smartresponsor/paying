<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$expectedReports = [
    'tools/inspection/PayingControllerNameFormReport.php',
    'tools/inspection/PayingServiceCoreNameFormReport.php',
    'tools/inspection/PayingApiBoundaryNameFormReport.php',
    'tools/inspection/PayingConsoleCommandNameFormReport.php',
    'tools/inspection/PayingInfrastructureNameFormReport.php',
    'tools/inspection/PayingBusinessServiceNameFormReport.php',
    'tools/inspection/PayingServiceAdapterNameFormReport.php',
    'tools/inspection/PayingWebhookControllerNameFormReport.php',
    'tools/inspection/PayingProviderServiceNameFormReport.php',
    'tools/inspection/PayingValueObjectExceptionNameFormReport.php',
    'tools/inspection/PayingEntityFirstPersistenceReport.php',
];

$residualLegacyPaths = [
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
    'src/Infrastructure/AuditLogger.php',
    'src/Infrastructure/DbalIdempotencyStore.php',
    'src/Infrastructure/MetricSubscriber.php',
    'src/Infrastructure/OutboxPublisher.php',
    'src/Infrastructure/OutboxWorker.php',
    'src/Infrastructure/PublisherTransportLog.php',
    'src/Infrastructure/RateLimitSubscriber.php',
    'src/Infrastructure/RedisIdempotencyStore.php',
    'src/Infrastructure/ResponseHeaderSubscriber.php',
    'src/Infrastructure/Console/DlqReplayCommand.php',
    'src/Infrastructure/Console/GateSloCommand.php',
    'src/Infrastructure/Console/IdemPurgeCommand.php',
    'src/Infrastructure/Console/OutboxRunCommand.php',
    'src/Infrastructure/Console/ProjectionRebuildCommand.php',
    'src/Infrastructure/Console/ProjectionSyncCommand.php',
    'src/Infrastructure/Console/ReconcileRunCommand.php',
    'src/Infrastructure/Console/SlaReportCommand.php',
    'src/InfrastructureInterface/AuditLoggerInterface.php',
    'src/InfrastructureInterface/OutboxPublisherInterface.php',
    'src/InfrastructureInterface/PublisherTransportInterface.php',
    'src/Service/ApiErrorResponseFactory.php',
    'src/Service/ApiJsonBodyDecoder.php',
    'src/Service/ApiRequestValidator.php',
    'src/Service/CircuitBreaker.php',
    'src/Service/DlqService.php',
    'src/Service/IdempotencyService.php',
    'src/Service/IdempotencyStoreFactory.php',
    'src/Service/InternalPaymentProvider.php',
    'src/Service/Metric.php',
    'src/Service/OidcJwksCache.php',
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
    'src/Service/WebhookIngestService.php',
    'src/Service/WebhookVerifier.php',
    'src/Service/Gateway/PayPalGateway.php',
    'src/Service/Gateway/StripeGateway.php',
    'src/Service/Mapper/AdyenEventMapper.php',
    'src/Service/Mapper/StripeEventMapper.php',
    'src/Service/Order/NullOrderPaymentSync.php',
    'src/Service/Webhook/JsonSchemaValidator.php',
    'src/Service/Webhook/PayPalEventNormalizer.php',
    'src/Service/Webhook/PayPalSignatureValidator.php',
    'src/Service/Webhook/StripeEventNormalizer.php',
    'src/Service/Webhook/StripeSignatureValidator.php',
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
    'src/Exception/OutboxOperationException.php',
];

$violations = [];

foreach ($expectedReports as $relativePath) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($path)) {
        $violations[] = 'Missing canonical report: ' . $relativePath;
    }
}

foreach ($residualLegacyPaths as $relativePath) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($path)) {
        $violations[] = 'Residual legacy duplicate still present: ' . $relativePath;
    }
}

$src = $root . DIRECTORY_SEPARATOR . 'src';
if (is_dir($src)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }
        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            $violations[] = 'Unable to read PHP file: ' . $file->getPathname();
            continue;
        }
        if (preg_match('/\\b(?:class|interface|trait|enum)\\s+PaymentPayment[A-Za-z0-9_]*/', $contents, $match) === 1) {
            $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $violations[] = 'Double Payment prefix detected in ' . $relativePath . ': ' . $match[0];
        }
    }
}

echo "Paying canonical nameEntity-form summary report\n";
echo "Expected reports: " . count($expectedReports) . "\n";
echo "Tracked residual legacy duplicates: " . count($residualLegacyPaths) . "\n";

if ($violations !== []) {
    echo "Status: FAIL\n";
    foreach ($violations as $violation) {
        echo " - " . $violation . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
