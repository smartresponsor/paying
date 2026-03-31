<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Gateway;

interface PaymentGatewayInterface
{
    public function code(): string;

    public function authorize(string $paymentId, int $amountMinor, string $currency): string;

    public function capture(string $paymentId, int $amountMinor, string $currency): string;

    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string;
}
