<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Entity\Payment;

/**
 * Defines the contract for the payment console refund handler interface payment service boundary.
 */
interface PaymentConsoleRefundHandlerInterface
{
    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(string $paymentId, string $amount, string $provider): ?Payment;
}
