<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testFromMinorCreatesDeterministicDecimalAmount(): void
    {
        $money = Money::fromMinor(1234, 'USD');

        self::assertSame(1234, $money->amountMinor());
        self::assertSame('USD', $money->currency());
        self::assertSame('12.34', $money->toDecimalString());
    }

    public function testFromDecimalStringCreatesCanonicalMoney(): void
    {
        $money = Money::fromDecimalString('10.05', 'USD');

        self::assertSame(1005, $money->amountMinor());
        self::assertSame('10.05', $money->toDecimalString());
    }

    public function testFromMinorRejectsNegativeValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount minor must be greater than or equal to zero.');

        Money::fromMinor(-1, 'USD');
    }

    public function testFromMinorRejectsInvalidCurrencyCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency must be a 3-letter uppercase ISO code.');

        Money::fromMinor(100, 'usd');
    }

    public function testFromDecimalStringRejectsInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be in decimal format like 10.00.');

        Money::fromDecimalString('10', 'USD');
    }
}
