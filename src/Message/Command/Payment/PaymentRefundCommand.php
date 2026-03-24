<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

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
