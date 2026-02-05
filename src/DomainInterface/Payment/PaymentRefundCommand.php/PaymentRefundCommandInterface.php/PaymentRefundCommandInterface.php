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

interface PaymentRefundCommandInterface
{
    public function __construct(public string $paymentId, public int $amountMinor, public string $currency, public ?string $reason = null, public ?string $idempotencyKey = null);
}
