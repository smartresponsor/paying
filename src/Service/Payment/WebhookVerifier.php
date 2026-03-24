<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

class WebhookVerifier implements WebhookVerifierInterface
{
    public function verify(string $provider, string $raw, array $headers): bool
    {
        $prov = strtolower($provider);
        if ('stripe' === $prov) {
            $sig = $this->headerValue($headers, 'stripe-signature');
            $secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
            if (null === $sig || '' === $secret) {
                return false;
            }
            $parts = [];
            foreach (explode(',', $sig) as $kv) {
                [$k, $v] = array_pad(explode('=', trim($kv), 2), 2, '');
                $parts[strtolower($k)] = $v;
            }
            if (!isset($parts['t']) || !isset($parts['v1'])) {
                return false;
            }
            $signedPayload = $parts['t'].'.'.$raw;
            $expected = hash_hmac('sha256', $signedPayload, $secret);

            return hash_equals($expected, $parts['v1']);
        }
        if ('adyen' === $prov) {
            $hmac = $this->headerValue($headers, 'hmac-signature');
            $secret = $_ENV['ADYEN_HMAC_SECRET'] ?? '';
            if (null === $hmac || '' === $secret) {
                return false;
            }
            $decodedSecret = base64_decode($secret, true);
            if (false === $decodedSecret) {
                return false;
            }
            $expected = base64_encode(hash_hmac('sha256', $raw, $decodedSecret, true));

            return hash_equals($expected, $hmac);
        }

        return filter_var($_ENV['PAYMENT_WEBHOOK_ALLOW_UNKNOWN'] ?? false, FILTER_VALIDATE_BOOL);
    }

    /**
     * @param array<string, string|list<string>> $headers
     */
    private function headerValue(array $headers, string $name): ?string
    {
        $value = $headers[strtolower($name)] ?? $headers[$name] ?? null;
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if (null === $value) {
            return null;
        }

        $normalized = trim((string) $value);

        return '' === $normalized ? null : $normalized;
    }
}
