<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Payment;
use App\Entity\Payment\Payment;

interface PaymentServiceInterface
{
    public function create(string $orderId, int $amountMinor, string $currency): Payment;
}
