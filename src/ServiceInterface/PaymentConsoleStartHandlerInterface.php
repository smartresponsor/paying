<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Entity\Payment;

/**
 * Defines the contract for the payment console start handler interface payment service boundary.
 */
interface PaymentConsoleStartHandlerInterface
{
    /**
     * Executes the start operation for the current payment workflow.
     */
    public function start(string $orderId, string $provider, string $amount, string $currency): Payment;
}
