<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Service\PaymentStartResult;

/**
 * Defines the contract for the payment start service interface payment service boundary.
 */
interface PaymentStartServiceInterface
{
    /**
     * Executes the start operation for the current payment workflow.
     */
    public function start(string $orderId, string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult;

    /**
     * Provides the restart behavior for the payment start service interface component.
     */
    public function restart(string $paymentId, string $provider, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult;
}
