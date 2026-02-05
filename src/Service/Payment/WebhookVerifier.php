<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\WebhookVerifierInterface;

class WebhookVerifier implements WebhookVerifierInterface
{
    public function verify(string $provider, string $raw, array $headers): bool
    {
        $prov = strtolower($provider);
        if ($prov === 'stripe') {
            $sig = $headers['stripe-signature'] ?? $headers['Stripe-Signature'] ?? null;
            $secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
            if (!$sig || !$secret) {
                return false;
            }
            // Minimal check: signature header must contain the computed HMAC for some t= timestamp.
            $parts = [];
            foreach (explode(',', (string)$sig) as $kv) {
                [$k,$v] = array_pad(explode('=', trim($kv), 2), 2, '');
                $parts[strtolower($k)] = $v;
            }
            if (!isset($parts['t']) || !isset($parts['v1'])) {
                return false;
            }
            $signedPayload = $parts['t'] . '.' . $raw;
            $expected = hash_hmac('sha256', $signedPayload, $secret);
            return hash_equals($expected, $parts['v1']);
        }
        if ($prov === 'adyen') {
            $hmac = $headers['Hmac-Signature'] ?? $headers['hmac-signature'] ?? null;
            $secret = $_ENV['ADYEN_HMAC_SECRET'] ?? '';
            if (!$hmac || !$secret) {
                return false;
            }
            $expected = base64_encode(hash_hmac('sha256', $raw, base64_decode($secret), true));
            return hash_equals($expected, (string)$hmac);
        }
        // Unknown providers default to true only if explicitly allowed
        return (bool)($_ENV['PAYMENT_WEBHOOK_ALLOW_UNKNOWN'] ?? false);
    }
}
