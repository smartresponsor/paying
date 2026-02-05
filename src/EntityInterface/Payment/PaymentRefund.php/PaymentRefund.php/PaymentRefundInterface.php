<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\EntityInterface\Payment;

interface PaymentRefundInterface
{
    public function __construct(string $id, string $paymentId, int $amountMinor, string $currency, ?string $reason = null);
}
