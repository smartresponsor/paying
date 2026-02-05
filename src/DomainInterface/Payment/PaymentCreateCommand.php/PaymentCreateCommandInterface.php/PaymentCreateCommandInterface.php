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

interface PaymentCreateCommandInterface
{
    public function __construct(public string $orderId, public int $amountMinor, public string $currency, public string $gatewayCode, public ?string $idempotencyKey = null);
}
