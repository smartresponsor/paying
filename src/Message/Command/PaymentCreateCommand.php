<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Command;

final class PaymentCreateCommand
{
    public function __construct(
        public string $orderId,
        public int $amountMinor,
        public string $currency,
        public string $gatewayCode,
        public ?string $idempotencyKey = null,
    ) {
    }
}
