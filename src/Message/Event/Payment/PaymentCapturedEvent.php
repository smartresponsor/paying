<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Message\Event\Payment;

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
