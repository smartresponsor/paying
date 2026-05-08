<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$required = [
    'src/Subscriber/PaymentMetricSubscriber.php',
    'src/Subscriber/PaymentRateLimitSubscriber.php',
    'src/Subscriber/PaymentResponseHeaderSubscriber.php',
    'src/Subscriber/PaymentScopeGuardSubscriber.php',
    'tests/Unit/PaymentResponseHeaderSubscriberTest.php',
    'tests/Unit/PaymentScopeGuardSubscriberTest.php',
];

$forbidden = [
    'src/Infrastructure/PaymentMetricSubscriber.php',
    'src/Infrastructure/PaymentRateLimitSubscriber.php',
    'src/Infrastructure/PaymentResponseHeaderSubscriber.php',
    'src/Infrastructure/ScopeGuardSubscriber.php',
    'tests/Unit/ResponseHeaderSubscriberTest.php',
    'tests/Unit/ScopeGuardSubscriberTest.php',
];

$failures = [];
foreach ($required as $relativePath) {
    if (!is_file($root.'/'.$relativePath)) {
        $failures[] = 'Missing canonical subscriber-layer file: '.$relativePath;
    }
}

foreach ($forbidden as $relativePath) {
    if (is_file($root.'/'.$relativePath)) {
        $failures[] = 'Legacy subscriber-layer file still present: '.$relativePath;
    }
}

$serviceConfig = is_file($root.'/config/services.yaml') ? file_get_contents($root.'/config/services.yaml') : '';
foreach ([
    'App\\Paying\\Subscriber\\PaymentRateLimitSubscriber',
    'App\\Paying\\Subscriber\\PaymentResponseHeaderSubscriber',
] as $expectedServiceId) {
    if (!str_contains((string) $serviceConfig, $expectedServiceId)) {
        $failures[] = 'Missing subscriber service configuration: '.$expectedServiceId;
    }
}

foreach ([
    'App\\Paying\\Infrastructure\\PaymentRateLimitSubscriber',
    'App\\Paying\\Infrastructure\\PaymentResponseHeaderSubscriber',
] as $legacyServiceId) {
    if (str_contains((string) $serviceConfig, $legacyServiceId)) {
        $failures[] = 'Legacy infrastructure subscriber service id still configured: '.$legacyServiceId;
    }
}

if ([] !== $failures) {
    echo "Paying subscriber layer/name-form report: FAIL\n";
    foreach ($failures as $failure) {
        echo '- '.$failure."\n";
    }
    exit(1);
}

echo "Paying subscriber layer/name-form report: OK\n";
exit(0);
