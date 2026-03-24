<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Message\Command\Payment;

final class PaymentRefundCommand
{
    public function __construct(
        public string $paymentId,
        public int $amountMinor,
        public string $currency,
        public ?string $reason = null,
        public ?string $idempotencyKey = null,
    ) {
    }
}
