<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;

use App\Entity\Payment\Payment;

interface PaymentStartServiceInterface
{
    /**
     * @return array{payment: Payment, providerRef: ?string, result: array<string, mixed>}
     */
    public function start(string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): array;
}
