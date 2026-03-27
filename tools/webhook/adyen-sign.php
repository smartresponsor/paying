<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$secretB64 = $_ENV['ADYEN_HMAC_SECRET'] ?? ($argv[1] ?? '');
$payload = $argv[2] ?? '{"test":"ok"}';
if ('' === $secretB64) {
    fwrite(STDERR, "secret required\n");
    exit(2);
}
$raw = base64_decode($secretB64);
$hmac = base64_encode(hash_hmac('sha256', $payload, $raw, true));
echo "Hmac-Signature: {$hmac}\n";
