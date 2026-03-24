<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment\Webhook;

use App\Service\Payment\WebhookVerifierInterface;

final class StripeSignatureValidator
{
    public function __construct(private readonly WebhookVerifierInterface $verifier)
    {
    }

    public function isValid(string $payload, ?string $signature): bool
    {
        return $this->verifier->verify('stripe', $payload, [
            'stripe-signature' => $signature,
            'Stripe-Signature' => $signature,
        ]);
    }
}
