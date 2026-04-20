<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ValueObject;

/**
 * Represents the gateway code value object used by the payment lifecycle and related contracts.
 */
final class GatewayCode
{
    private const array ALLOWED = ['stripe', 'paypal', 'authorize'];

    public function __construct(private readonly string $value)
    {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new \InvalidArgumentException('Unsupported gateway code: '.$value);
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
