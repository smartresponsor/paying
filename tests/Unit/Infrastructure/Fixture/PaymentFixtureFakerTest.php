<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Fixture;

use App\Infrastructure\Fixture\PaymentFixtureFaker;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the payment fixture faker scenario within the payment fixture test surface.
 */
final class PaymentFixtureFakerTest extends TestCase
{
    /**
     * Verifies that amounts and provider references are deterministic.
     */
    public function testAmountsAndProviderReferencesAreDeterministic(): void
    {
        $first = new PaymentFixtureFaker();
        $second = new PaymentFixtureFaker();

        self::assertSame($first->amount(), $second->amount());
        self::assertSame($first->providerReference('stripe'), $second->providerReference('stripe'));
        self::assertSame($first->amount(), $second->amount());
    }
}
