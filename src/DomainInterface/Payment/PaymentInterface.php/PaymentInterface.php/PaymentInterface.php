<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\DomainInterface\Payment;

interface PaymentInterface
{
    public function __construct(string $id, string $orderId, int $amountMinor, string $currency);
    public function id();
    public function orderId();
    public function amountMinor();
    public function currency();
    public function status();
    public function markCaptured();
    public function markFailed();
}
