<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Fixture;

use App\Infrastructure\Fixture\PaymentFixtureFaker;
use PHPUnit\Framework\TestCase;

final class PaymentFixtureFakerTest extends TestCase
{
    public function testAmountsAndProviderReferencesAreDeterministic(): void
    {
        $first = new PaymentFixtureFaker();
        $second = new PaymentFixtureFaker();

        self::assertSame($first->amount(), $second->amount());
        self::assertSame($first->providerReference('stripe'), $second->providerReference('stripe'));
        self::assertSame($first->amount(), $second->amount());
    }
}
