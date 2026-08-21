<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Webhook;

use App\Paying\ServiceInterface\PaymentWebhookVerifierServiceInterface;

/**
 * Provides the stripe signature validator step for webhook validation and normalization flows.
 */
final readonly class PaymentStripeSignatureValidator
{
    public function __construct(private PaymentWebhookVerifierServiceInterface $verifier)
    {
    }

    /**
     * Determines whether the is valid condition is currently satisfied.
     */
    public function isValid(string $payload, ?string $signature): bool
    {
        return $this->verifier->verify('stripe', $payload, [
            'stripe-signature' => $signature,
            'Stripe-Signature' => $signature,
        ]);
    }
}
