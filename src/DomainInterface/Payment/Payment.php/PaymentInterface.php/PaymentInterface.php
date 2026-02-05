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

interface PaymentInterface
{
    public function __construct(string $id, string $orderId, int $amountMinor, string $currency);
    public function id(): string;
    public function orderId(): string;
    public function amountMinor(): int;
    public function currency(): string;
    public function status(): string;
    public function markCaptured(): void;
    public function markFailed(): void;
}
