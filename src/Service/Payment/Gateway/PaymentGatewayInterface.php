<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment\Gateway;

interface PaymentGatewayInterface
{
    public function code(): string;

    public function authorize(string $paymentId, int $amountMinor, string $currency): string;

    public function capture(string $paymentId, int $amountMinor, string $currency): string;

    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string;
}
