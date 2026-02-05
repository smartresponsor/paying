<?php
namespace App\Domain\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\Domain\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\ValueObject\Payment;

final class PaymentReference
{
    public function __construct(private string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('PaymentReference cannot be empty');
        }
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
