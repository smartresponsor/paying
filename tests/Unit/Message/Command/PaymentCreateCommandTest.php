<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Message\Handler;

use App\Message\Command\PaymentCreateCommand;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the payment create command scenario within the payment command test surface.
 */
final class PaymentCreateCommandTest extends TestCase
{
    /**
     * Verifies that canonical provider code falls back to gateway code.
     */
    public function testCanonicalProviderCodeFallsBackToGatewayCode(): void
    {
        $command = new PaymentCreateCommand('order-1', 1000, 'usd', 'stripe');
        $command->providerCode = '';

        self::assertSame('stripe', $command->canonicalProviderCode());
    }
}
