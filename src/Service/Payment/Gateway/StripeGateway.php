<?php
namespace OrderComponent\Payment\Service\Payment\Gateway;

use OrderComponent\Payment\Contract\ServiceInterface\Payment\PaymentGatewayInterface;

final class StripeGateway implements PaymentGatewayInterface
{
    public function code(): string { return 'stripe'; }

    public function authorize(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'stripe_auth_' . $paymentId;
    }

    public function capture(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'stripe_capture_' . $paymentId;
    }

    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string
    {
        return 'stripe_refund_' . $paymentId;
    }
}