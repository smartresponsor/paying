<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;

interface PaymentConsoleRefundHandlerInterface
{
    public function refund(string $paymentId, string $amount, string $provider): ?Payment;
}
