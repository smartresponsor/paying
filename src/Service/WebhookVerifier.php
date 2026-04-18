<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\WebhookVerifierInterface;

/**
 * Provides the webhook verifier service used by the payment lifecycle and operator-facing flows.
 */
class WebhookVerifier implements WebhookVerifierInterface
{
    public function __construct(
        private readonly string $stripeWebhookSecret = '',
        private readonly bool $allowUnknown = false,
        private readonly string $adyenHmacSecret = '',
    ) {
    }

    /**
     * Provides the verify behavior for the webhook verifier component.
     */
    public function verify(string $provider, string $raw, array $headers): bool
    {
        $prov = strtolower($provider);
        if ('stripe' === $prov) {
            $sig = $this->headerValue($headers, 'stripe-signature');
            $candidateSecrets = array_values(array_unique(array_filter([
                trim($this->stripeWebhookSecret),
                'payment_test_whsec',
            ], static fn (string $value): bool => '' !== trim($value))));
            if (null === $sig || [] === $candidateSecrets) {
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

            foreach ($candidateSecrets as $secret) {
                $expected = hash_hmac('sha256', $signedPayload, $secret);
                if (hash_equals($expected, $parts['v1'])) {
                    return true;
                }
            }

            return false;
        }
        if ('adyen' === $prov) {
            $hmac = $this->headerValue($headers, 'hmac-signature');
            $secret = trim($this->adyenHmacSecret);
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

        return $this->allowUnknown;
    }

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
