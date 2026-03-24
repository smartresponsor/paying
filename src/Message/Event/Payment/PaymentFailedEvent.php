<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Message\Event\Payment;

final class PaymentFailedEvent
{
    public function __construct(
        public string $paymentId,
        public string $errorCode,
        public ?string $message = null,
    ) {
    }
}
