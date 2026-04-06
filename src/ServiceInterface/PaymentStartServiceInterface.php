<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Service\PaymentStartResult;

interface PaymentStartServiceInterface
{
    public function start(string $orderId, string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult;

    public function restart(string $paymentId, string $provider, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult;
}
