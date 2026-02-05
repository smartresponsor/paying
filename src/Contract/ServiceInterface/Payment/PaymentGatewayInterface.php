<?php
namespace OrderComponent\Payment\Contract\ServiceInterface\Payment;

interface PaymentGatewayInterface
{
    public function authorize(string $paymentId, int $amountMinor, string $currency): string;
    public function capture(string $paymentId, int $amountMinor, string $currency): string;
    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string;
}
