<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Message\Handler;

use App\Message\Command\PaymentCreateCommand;
use PHPUnit\Framework\TestCase;

final class PaymentCreateCommandTest extends TestCase
{
    public function testCanonicalProviderCodeFallsBackToGatewayCode(): void
    {
        $command = new PaymentCreateCommand('order-1', 1000, 'usd', 'stripe');
        $command->providerCode = '';

        self::assertSame('stripe', $command->canonicalProviderCode());
    }
}
