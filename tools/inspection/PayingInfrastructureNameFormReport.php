<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * Reports infrastructure-layer classes that still miss the Payment prefix.
 *
 * This is intentionally report-only. It protects the Paying component from
 * reintroducing short infrastructure names such as AuditLogger or OutboxWorker
 * after the Wave 6 name-form canonicalization.
 */

$root = dirname(__DIR__, 2);

$forbidden = [
    'src/Infrastructure/AuditLogger.php',
    'src/Infrastructure/OutboxPublisher.php',
    'src/Infrastructure/OutboxWorker.php',
    'src/Infrastructure/DbalIdempotencyStore.php',
    'src/Infrastructure/RedisIdempotencyStore.php',
    'src/Infrastructure/RateLimitSubscriber.php',
    'src/Infrastructure/ResponseHeaderSubscriber.php',
    'src/Infrastructure/MetricSubscriber.php',
    'src/Infrastructure/PublisherTransportLog.php',
    'src/InfrastructureInterface/AuditLoggerInterface.php',
    'src/InfrastructureInterface/OutboxPublisherInterface.php',
    'src/InfrastructureInterface/PublisherTransportInterface.php',
];

$missing = [];
foreach ($forbidden as $relativePath) {
    if (is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        $missing[] = $relativePath;
    }
}

$required = [
    'src/Infrastructure/PaymentAuditLogger.php',
    'src/Infrastructure/PaymentOutboxPublisher.php',
    'src/Infrastructure/PaymentOutboxWorker.php',
    'src/Infrastructure/PaymentDbalIdempotencyStore.php',
    'src/Infrastructure/PaymentRedisIdempotencyStore.php',
    'src/Infrastructure/PaymentRateLimitSubscriber.php',
    'src/Infrastructure/PaymentResponseHeaderSubscriber.php',
    'src/Infrastructure/PaymentMetricSubscriber.php',
    'src/Infrastructure/PaymentPublisherTransportLog.php',
    'src/InfrastructureInterface/PaymentAuditLoggerInterface.php',
    'src/InfrastructureInterface/PaymentOutboxPublisherInterface.php',
    'src/InfrastructureInterface/PaymentPublisherTransportInterface.php',
];

$absent = [];
foreach ($required as $relativePath) {
    if (!is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        $absent[] = $relativePath;
    }
}

if ($missing !== [] || $absent !== []) {
    fwrite(STDERR, "Paying infrastructure name-form report: FAIL\n");

    foreach ($missing as $path) {
        fwrite(STDERR, " - legacy unprefixed file remains: {$path}\n");
    }

    foreach ($absent as $path) {
        fwrite(STDERR, " - required prefixed file is missing: {$path}\n");
    }

    exit(1);
}

fwrite(STDOUT, "Paying infrastructure name-form report: OK\n");
