<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Service\PaymentApiStartHandler;
use App\Paying\Service\PaymentStartResult;
use App\Paying\ServiceInterface\IdempotencyServiceInterface;
use App\Paying\ServiceInterface\PaymentStartInput;
use App\Paying\ServiceInterface\PaymentStartServiceInterface;
use App\Paying\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment api start handler scenario within the payment unit test surface.
 */
final class PaymentApiStartHandlerTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that handle returns api payload via idempotency gate.
     *
     * @throws \JsonException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testHandleReturnsApiPayloadViaIdempotencyGate(): void
    {
        $input = new PaymentStartInput('order-2001', 'internal', '12.50', 'USD');

        $payment = new PaymentEntity(new Ulid(), PaymentStatus::processing, '12.50', 'USD');

        $startService = $this->createMock(PaymentStartServiceInterface::class);
        $startService
            ->expects(self::once())
            ->method('start')
            ->with('order-2001', 'internal', '12.50', 'USD', 'idem-1', 'api')
            ->willReturn(new PaymentStartResult($payment, 'ref-1', ['ok' => true]));

        $idem = $this->createMock(IdempotencyServiceInterface::class);
        $idem
            ->expects(self::once())
            ->method('execute')
            ->with('idem-1', 'hash-1', self::isType('callable'))
            ->willReturnCallback(static fn (string $key, string $hash, callable $callback): array => $callback());

        $handler = new PaymentApiStartHandler($startService, $idem);
        $result = $handler->handle($input, 'idem-1', 'hash-1');

        self::assertSame((string) $payment->id(), $result['payment']);
        self::assertSame('internal', $result['provider']);
        self::assertSame('processing', $result['status']);
        self::assertSame('ref-1', $result['providerRef']);
        self::assertSame(['ok' => true], $result['result']);
    }
}
