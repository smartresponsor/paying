<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Event;

/**
 * Represents the payment refunded event notification emitted by the payment messaging layer.
 */
final class PaymentRefundedEvent
{
    public function __construct(
        public string $paymentId,
        public int $amountMinor,
        public string $currency,
        public ?string $gatewayTransactionId = null,
        public ?string $reason = null,
    ) {
    }
}
