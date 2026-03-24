<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

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
