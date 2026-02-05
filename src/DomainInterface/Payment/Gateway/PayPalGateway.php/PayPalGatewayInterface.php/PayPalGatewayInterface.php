<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\DomainInterface\Payment;

interface PayPalGatewayInterface
{
    public function code(): string;
    public function authorize(string $paymentId, int $amountMinor, string $currency): string;
    public function capture(string $paymentId, int $amountMinor, string $currency): string;
    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string;
}
