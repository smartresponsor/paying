<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ValueObject;

/**
 * Represents the payment reference value object used by the payment lifecycle and related contracts.
 */
final class PaymentReference
{
    public function __construct(private readonly string $value)
    {
        if ('' === $value) {
            throw new \InvalidArgumentException('PaymentReference cannot be empty');
        }
    }

    /**
     * Returns the normalized scalar representation carried by this value object.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Returns the string representation used when the value object is rendered in logs or contracts.
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
