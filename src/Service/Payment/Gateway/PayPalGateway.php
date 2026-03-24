<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment\Gateway;

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
