<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

final readonly class PaymentStartInput
{
    public function __construct(
        public string $orderId,
        public string $provider,
        public string $amount,
        public string $currency,
    ) {
    }
}
