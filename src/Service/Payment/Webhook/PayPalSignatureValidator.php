<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment\Webhook;

use App\Service\Payment\WebhookVerifierInterface;

final class PayPalSignatureValidator
{
    public function __construct(private readonly WebhookVerifierInterface $verifier)
    {
    }

    public function isValid(string $payload, array $headers): bool
    {
        return $this->verifier->verify('paypal', $payload, $headers);
    }
}
