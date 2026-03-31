<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ValueObject;

final readonly class Money
{
    public function __construct(
        private int $amountMinor,
        private string $currency,
    ) {
        if ($amountMinor < 0) {
            throw new \InvalidArgumentException('Amount minor must be greater than or equal to zero.');
        }

        if (1 !== preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new \InvalidArgumentException('Currency must be a 3-letter uppercase ISO code.');
        }
    }

    public static function fromMinor(int $amountMinor, string $currency): self
    {
        return new self($amountMinor, $currency);
    }

    public static function fromDecimalString(string $amount, string $currency): self
    {
        if (1 !== preg_match('/^\d+(\.\d{2})$/', $amount)) {
            throw new \InvalidArgumentException('Amount must be in decimal format like 10.00.');
        }

        [$major, $minor] = explode('.', $amount, 2);

        return new self(((int) $major * 100) + (int) $minor, $currency);
    }

    public function amountMinor(): int
    {
        return $this->amountMinor;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function toDecimalString(): string
    {
        return number_format($this->amountMinor / 100, 2, '.', '');
    }
}
