<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\Service\IdempotencyService;
use App\Service\PaymentApiStartHandler;
use App\Service\PaymentStartResult;
use App\Service\PaymentStartInput;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentApiStartHandlerTest extends TestCase
{
    public function testHandleReturnsApiPayloadViaIdempotencyGate(): void
    {
        $input = new PaymentStartInput('internal', '12.50', 'USD');

        $payment = new Payment(new Ulid(), PaymentStatus::processing, '12.50', 'USD');

        $startService = $this->createMock(PaymentStartServiceInterface::class);
        $startService
            ->expects(self::once())
            ->method('start')
            ->with('internal', '12.50', 'USD', 'idem-1', 'api')
            ->willReturn(new PaymentStartResult($payment, 'ref-1', ['ok' => true]));

        $idem = $this->createMock(IdempotencyService::class);
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
