<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the payment console read model interface payment service boundary.
 */
interface PaymentConsoleReadModelInterface
{
    /**
     *     payments: list<array{id: string, orderId: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string}>,
     *     selectedPayment: array{id: string, orderId: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string}|null,
     *     events: list<array{id: string, provider: string, externalEventId: string, status: string, receivedAt: string}>,
     *     filters: array{q: string, status: string}
     * }
     *
     * @return array{
     */
    public function build(string $query, string $status, string $selectedPaymentId): array;
}
