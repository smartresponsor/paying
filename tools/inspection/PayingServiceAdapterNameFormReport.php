<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$forbidden = [
    'src/Service/Gateway/PayPalGateway.php',
    'src/Service/Gateway/StripeGateway.php',
    'src/Service/Mapper/AdyenEventMapper.php',
    'src/Service/Mapper/StripeEventMapper.php',
    'src/Service/Order/NullOrderPaymentSync.php',
    'src/Service/Webhook/JsonSchemaValidator.php',
    'src/Service/Webhook/PayPalEventNormalizer.php',
    'src/Service/Webhook/StripeEventNormalizer.php',
    'src/Service/Webhook/PayPalSignatureValidator.php',
    'src/Service/Webhook/StripeSignatureValidator.php',
    'src/Service/WebhookIngestService.php',
    'src/Service/WebhookVerifier.php',
    'src/Service/ReconciliationService.php',
    'src/Service/RefundService.php',
];
$expected = [
    'src/Service/Gateway/PaymentPayPalGateway.php',
    'src/Service/Gateway/PaymentStripeGateway.php',
    'src/Service/Mapper/PaymentAdyenEventMapper.php',
    'src/Service/Mapper/PaymentStripeEventMapper.php',
    'src/Service/Order/PaymentNullOrderPaymentSync.php',
    'src/Service/Webhook/PaymentJsonSchemaValidator.php',
    'src/Service/Webhook/PaymentPayPalEventNormalizer.php',
    'src/Service/Webhook/PaymentStripeEventNormalizer.php',
    'src/Service/Webhook/PaymentPayPalSignatureValidator.php',
    'src/Service/Webhook/PaymentStripeSignatureValidator.php',
];
$errors = [];
foreach ($forbidden as $path) { if (is_file($root.'/'.$path)) { $errors[] = 'Forbidden legacy file remains: '.$path; } }
foreach ($expected as $path) { if (!is_file($root.'/'.$path)) { $errors[] = 'Expected canonical file is missing: '.$path; } }
if ($errors !== []) { fwrite(STDERR, "Paying service adapter name-form report: FAILED\n".implode("\n", $errors)."\n"); exit(1); }
echo "Paying service adapter name-form report: OK\n";
