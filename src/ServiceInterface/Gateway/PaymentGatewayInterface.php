<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface\Gateway;

/**
 * Defines the contract for the payment gateway interface payment gateway operations.
 */
interface PaymentGatewayInterface
{
    /**
     * Returns the value exposed by the code accessor.
     */
    public function code(): string;

    /**
     * Executes the authorize operation for the current payment workflow.
     */
    public function authorize(string $paymentId, int $amountMinor, string $currency): string;

    /**
     * Executes the capture operation for the current payment workflow.
     */
    public function capture(string $paymentId, int $amountMinor, string $currency): string;

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string;
}
