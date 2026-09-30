<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ValueObject;

/**
 * Represents the money value object used by the payment lifecycle and related contracts.
 */
final readonly class PaymentMoney
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

    /**
     * Builds a money value object from an amount that is already expressed in minor units.
     */
    public static function fromMinor(int $amountMinor, string $currency): self
    {
        return new self($amountMinor, $currency);
    }

    /**
     * Builds a money value object from a decimal string used by external payment-facing inputs.
     */
    public static function fromDecimalString(string $amount, string $currency): self
    {
        if (1 !== preg_match('/^\d+(\.\d{2})$/', $amount)) {
            throw new \InvalidArgumentException('Amount must be in decimal format like 10.00.');
        }

        [$major, $minor] = explode('.', $amount, 2);

        return new self(((int) $major * 100) + (int) $minor, $currency);
    }

    /**
     * Returns the stored amount in minor currency units for persistence and transport boundaries.
     */
    public function amountMinor(): int
    {
        return $this->amountMinor;
    }

    /**
     * Returns the three-letter ISO currency code associated with the stored monetary amount.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Formats the stored minor-unit amount as a decimal string for display and provider payloads.
     */
    public function toDecimalString(): string
    {
        return number_format($this->amountMinor / 100, 2, '.', '');
    }
}
