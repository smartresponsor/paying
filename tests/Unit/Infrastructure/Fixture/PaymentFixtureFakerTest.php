<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Fixture;

use App\Infrastructure\Fixture\PaymentFixtureFaker;
use PHPUnit\Framework\TestCase;

final class PaymentFixtureFakerTest extends TestCase
{
    public function testAmountsAndProviderReferencesAreDeterministic(): void
    {
        $faker = new PaymentFixtureFaker();

        self::assertSame('99.16', $faker->amount());
        self::assertSame('stripe_31_920484', $faker->providerReference('stripe'));
        self::assertSame('86.57', $faker->amount());
    }
}
