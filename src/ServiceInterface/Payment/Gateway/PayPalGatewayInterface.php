<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface PayPalGatewayInterface
{
    public function code();
    public function authorize(string $paymentId, int $amountMinor, string $currency);
    public function capture(string $paymentId, int $amountMinor, string $currency);
    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null);
}
