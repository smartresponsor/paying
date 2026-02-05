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

interface PaymentRefundedEventInterface
{
    public function __construct(public string $paymentId, public int $amountMinor, public string $currency, public ?string $gatewayTransactionId = null, public ?string $reason = null);
}
