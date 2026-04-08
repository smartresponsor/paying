<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

/**
 * Provides the payment not found exception service used by the payment lifecycle and operator-facing flows.
 */
final class PaymentNotFoundException extends \RuntimeException
{
    /**
     * Creates a descriptive exception for payment lookups that fail by identifier.
     */
    public static function byId(string $paymentId): self
    {
        return new self('Payment not found: '.$paymentId);
    }
}
