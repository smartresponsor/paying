<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the payment api start handler interface payment service boundary.
 */
interface PaymentApiStartHandlerInterface
{
    /**
     * Executes the handle operation for the current payment workflow.
     *
     * @return array{payment: string, orderId: string, provider: string, status: string, providerRef: string|null, result: array<string, mixed>}
     */
    public function handle(PaymentStartInput $input, string $idempotencyKey, string $payloadHash): array;
}
