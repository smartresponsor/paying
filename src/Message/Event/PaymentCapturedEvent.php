<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Event;

final class PaymentCapturedEvent
{
    public function __construct(
        public string $paymentId,
        public int $amountMinor,
        public string $currency,
        public ?string $gatewayTransactionId = null,
    ) {
    }
}
