<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Event;

/**
 * Represents the payment failed event notification emitted by the payment messaging layer.
 */
final class PaymentFailedEvent
{
    public function __construct(
        public string $paymentId,
        public string $errorCode,
        public ?string $message = null,
    ) {
    }
}
