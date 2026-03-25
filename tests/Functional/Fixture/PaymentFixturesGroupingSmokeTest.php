<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Fixture;

use App\Infrastructure\Fixture\PaymentFixture;
use App\Infrastructure\Fixture\PaymentGatewayFixture;
use App\Infrastructure\Fixture\PaymentMethodFixture;
use App\Infrastructure\Fixture\PaymentWebhookLogFixture;
use PHPUnit\Framework\TestCase;

final class PaymentFixturesGroupingSmokeTest extends TestCase
{
    public function testAllOwnedPaymentFixturesBelongToPaymentGroup(): void
    {
        self::assertSame(['payment'], PaymentFixture::getGroups());
        self::assertSame(['payment'], PaymentGatewayFixture::getGroups());
        self::assertSame(['payment'], PaymentMethodFixture::getGroups());
        self::assertSame(['payment'], PaymentWebhookLogFixture::getGroups());
    }
}
