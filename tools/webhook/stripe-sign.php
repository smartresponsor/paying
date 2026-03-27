<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? ($argv[1] ?? '');
$payload = $argv[2] ?? '{"test":"ok"}';
if ($secret === '') {
    fwrite(STDERR, "secret required\n");
    exit(2);
}
$t = time();
$signed = $t . '.' . $payload;
$hmac = hash_hmac('sha256', $signed, $secret);
echo "Stripe-Signature: t={$t},v1={$hmac}\n";
