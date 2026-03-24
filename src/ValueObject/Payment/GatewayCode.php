<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\ValueObject\Payment;

final class GatewayCode
{
    private const ALLOWED = ['stripe', 'paypal', 'authorize'];

    public function __construct(private string $value)
    {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new \InvalidArgumentException('Unsupported gateway code: '.$value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
