<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;

/**
 * Defines the contract for the payment service interface payment service boundary.
 */
interface PaymentServiceInterface
{
    /**
     * Executes the create operation for the current payment workflow.
     */
    public function create(string $orderId, int $amountMinor, string $currency): Payment;
}
