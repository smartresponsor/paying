<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Event\Payment;

final class PaymentEvent
{
    public function __construct(
        private readonly string $paymentId,
        private readonly string $status,
    ) {
    }

    public function paymentId(): string
    {
        return $this->paymentId;
    }

    public function status(): string
    {
        return $this->status;
    }
}
