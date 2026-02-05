<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface PaymentServiceInterface
{
    public function __construct(private PaymentRepositoryInterface $repo);
    public function create(string $orderId, int $amountMinor, string $currency);
}
