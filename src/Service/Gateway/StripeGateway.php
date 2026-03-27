<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Gateway;

use App\ServiceInterface\Gateway\PaymentGatewayInterface;

final class StripeGateway implements PaymentGatewayInterface
{
    public function code(): string
    {
        return 'stripe';
    }

    public function authorize(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'stripe_auth_'.$paymentId;
    }

    public function capture(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'stripe_capture_'.$paymentId;
    }

    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string
    {
        return 'stripe_refund_'.$paymentId;
    }
}
