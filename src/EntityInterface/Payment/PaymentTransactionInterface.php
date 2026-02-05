<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\EntityInterface\Payment;

interface PaymentTransactionInterface
{
    public function __construct(string $id, string $paymentId, string $gatewayTransactionId, string $type, int $amountMinor);
}
