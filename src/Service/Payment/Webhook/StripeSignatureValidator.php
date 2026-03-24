<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

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
