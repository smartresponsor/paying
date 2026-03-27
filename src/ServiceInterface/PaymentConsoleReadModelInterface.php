<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface PaymentConsoleReadModelInterface
{
    /**
     * @return array{payments: list<array<string, mixed>>, selectedPayment: ?array<string, mixed>, events: list<array<string, mixed>>, filters: array{q: string, status: string}}
     */
    public function build(string $query, string $status, string $selectedPaymentId): array;
}
