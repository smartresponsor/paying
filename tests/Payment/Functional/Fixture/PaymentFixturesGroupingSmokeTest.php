<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Tests\Payment\Functional\Fixture;

use App\Infrastructure\Payment\Fixture\PaymentFixture;
use App\Infrastructure\Payment\Fixture\PaymentGatewayFixture;
use App\Infrastructure\Payment\Fixture\PaymentMethodFixture;
use App\Infrastructure\Payment\Fixture\PaymentWebhookLogFixture;
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
