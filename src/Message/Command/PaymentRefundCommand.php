<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Message\Command;

/**
 * Carries the payment refund command payload across messenger-driven payment workflows.
 */
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
