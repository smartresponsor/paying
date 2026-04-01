<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;

interface PaymentConsoleStartHandlerInterface
{
    public function start(string $orderId, string $provider, string $amount, string $currency): Payment;
}
