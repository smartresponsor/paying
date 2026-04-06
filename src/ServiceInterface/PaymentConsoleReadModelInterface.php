<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface PaymentConsoleReadModelInterface
{
    /**
     * @return array{
     *     payments: list<array{id: string, orderId: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string}>,
     *     selectedPayment: array{id: string, orderId: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string}|null,
     *     events: list<array{id: string, provider: string, externalEventId: string, status: string, receivedAt: string}>,
     *     filters: array{q: string, status: string}
     * }
     */
    public function build(string $query, string $status, string $selectedPaymentId): array;
}
