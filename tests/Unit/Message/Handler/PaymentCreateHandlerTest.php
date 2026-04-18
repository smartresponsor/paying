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

/**
 * Exercises the payment create handler scenario within the payment handler test surface.
 */
final class PaymentCreateHandlerTest extends TestCase
{
    /**
     * Verifies that invoke uses provider code alias and messenger origin.
     */
    public function testInvokeUsesProviderCodeAliasAndMessengerOrigin(): void
    {
        $spy = new class implements PaymentStartServiceInterface {
            public array $calls = [];

            /**
             * Provides the start behavior required by this test scenario.
             */
            public function start(string $orderId, string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult
            {
                $this->calls[] = [
                    'method' => 'start',
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

            /**
             * Provides the restart behavior required by the extended payment start service contract.
             */
            public function restart(string $paymentId, string $provider, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult
            {
                $this->calls[] = [
                    'method' => 'restart',
                    'paymentId' => $paymentId,
                    'provider' => $provider,
                    'idempotencyKey' => $idempotencyKey,
                    'origin' => $origin,
                ];

                return new PaymentStartResult(
                    new Payment(new Ulid(), PaymentStatus::processing, '0.00', 'USD'),
                    null,
                    []
                );
            }
        };

        $handler = new PaymentCreateHandler($spy);
        $command = new PaymentCreateCommand('order-1001', 5050, 'usd', 'paypal', 'idem-1');

        $handler($command);

        self::assertCount(1, $spy->calls);

        $call = $spy->calls[0];

        self::assertSame('start', $call['method']);
        self::assertSame('order-1001', $call['orderId']);
        self::assertSame('paypal', $call['provider']);
        self::assertSame('50.50', $call['amount']);
        self::assertSame('USD', $call['currency']);
        self::assertSame('idem-1', $call['idempotencyKey']);
        self::assertSame('messenger-create', $call['origin']);
        self::assertSame('paypal', $command->providerCode);
        self::assertSame('paypal', $command->gatewayCode);
    }
}
