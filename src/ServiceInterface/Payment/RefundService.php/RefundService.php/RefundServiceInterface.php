<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface RefundServiceInterface
{
    public function createRefund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): PaymentRefund;
}
