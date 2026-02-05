<?php
namespace OrderComponent\Payment\Service\Payment\Gateway;

use OrderComponent\Payment\Contract\ServiceInterface\Payment\PaymentGatewayInterface;

final class PayPalGateway implements PaymentGatewayInterface
{
    public function code(): string { return 'paypal'; }

    public function authorize(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'paypal_auth_' . $paymentId;
    }

    public function capture(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'paypal_capture_' . $paymentId;
    }

    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string
    {
        return 'paypal_refund_' . $paymentId;
    }
}