<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Event;

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
