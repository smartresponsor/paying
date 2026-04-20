<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Event;

/**
 * Carries a compact payment lifecycle event payload between internal layers.
 */
final readonly class PaymentEvent
{
    public function __construct(
        private string $paymentId,
        private string $status,
    ) {
    }

    /**
     * Returns the payment identifier carried by this event.
     */
    public function paymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * Returns the payment status snapshot carried by this event.
     */
    public function status(): string
    {
        return $this->status;
    }
}
