<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Functional\Fixture;

use App\Paying\Infrastructure\Fixture\PaymentFixture;
use App\Paying\Infrastructure\Fixture\PaymentGatewayFixture;
use App\Paying\Infrastructure\Fixture\PaymentMethodFixture;
use App\Paying\Infrastructure\Fixture\PaymentWebhookLogFixture;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the payment fixtures grouping smoke scenario within the payment fixture test surface.
 */
final class PaymentFixturesGroupingSmokeTest extends TestCase
{
    /**
     * Verifies that all owned payment fixtures belong to payment group.
     */
    public function testAllOwnedPaymentFixturesBelongToPaymentGroup(): void
    {
        self::assertSame(['payment'], PaymentFixture::getGroups());
        self::assertSame(['payment'], PaymentGatewayFixture::getGroups());
        self::assertSame(['payment'], PaymentMethodFixture::getGroups());
        self::assertSame(['payment'], PaymentWebhookLogFixture::getGroups());
    }
}
