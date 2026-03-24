<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Gateway;
use App\ServiceInterface\Gateway\PaymentGatewayInterface;

final class PayPalGateway implements PaymentGatewayInterface
{
    public function code(): string
    {
        return 'paypal';
    }

    public function authorize(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'paypal_auth_'.$paymentId;
    }

    public function capture(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'paypal_capture_'.$paymentId;
    }

    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string
    {
        return 'paypal_refund_'.$paymentId;
    }
}
