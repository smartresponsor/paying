<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;

/**
 * Defines the contract for the payment console create handler interface payment service boundary.
 */
interface PaymentConsoleCreateHandlerInterface
{
    /**
     * Executes the create operation for the current payment workflow.
     */
    public function create(string $orderId, int $amountMinor, string $currency): Payment;
}
