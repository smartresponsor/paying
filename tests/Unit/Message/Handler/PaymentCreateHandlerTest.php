<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Message\Handler;

use App\Entity\Payment;
use App\Message\Command\PaymentCreateCommand;
use App\Message\Handler\PaymentCreateHandler;
use App\Service\PaymentStartResult;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentCreateHandlerTest extends TestCase
{
    public function testInvokeUsesProviderCodeAliasAndMessengerOrigin(): void
    {
        $spy = new class() implements PaymentStartServiceInterface {
            public array $calls = [];

            public function start(string $orderId, string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult
            {
                $this->calls[] = [
                    'orderId' => $orderId,
                    'provider' => $provider,
                    'amount' => $amount,
                    'currency' => $currency,
                    'idempotencyKey' => $idempotencyKey,
                    'origin' => $origin,
                ];

                return new PaymentStartResult(
                    new Payment(new Ulid(), PaymentStatus::processing, $amount, $currency, $orderId),
                    null,
                    []
                );
            }
        };

        $handler = new PaymentCreateHandler($spy);
        $command = new PaymentCreateCommand('order-1001', 5050, 'usd', 'paypal', 'idem-1');

        $handler($command);

        self::assertCount(1, $spy->calls);
        self::assertSame([
            'orderId' => 'order-1001',
            'provider' => 'paypal',
            'amount' => '50.50',
            'currency' => 'USD',
            'idempotencyKey' => 'idem-1',
            'origin' => 'messenger-create',
        ], $spy->calls[0]);
        self::assertSame('paypal', $command->providerCode);
        self::assertSame('paypal', $command->gatewayCode);
    }
}
