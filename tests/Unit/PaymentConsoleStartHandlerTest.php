<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\Payment;
use App\Paying\Service\PaymentConsoleStartHandler;
use App\Paying\Service\PaymentStartResult;
use App\Paying\ServiceInterface\PaymentStartServiceInterface;
use App\Paying\ValueObject\PaymentStatus;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment console start handler scenario within the payment unit test surface.
 */
final class PaymentConsoleStartHandlerTest extends TestCase
{
    /**
     * Verifies that start returns payment from start service.
     */
    public function testStartReturnsPaymentFromStartService(): void
    {
        $payment = new Payment(new Ulid(), PaymentStatus::processing, '12.50', 'USD');

        try {
            $startService = $this->createMock(PaymentStartServiceInterface::class);
        } catch (Exception $e) {
        }
        $startService
            ->expects(self::once())
            ->method('start')
            ->with('order-3001', 'internal', '12.50', 'USD', '', 'payment-console')
            ->willReturn(new PaymentStartResult($payment, null, []));

        $handler = new PaymentConsoleStartHandler($startService);

        self::assertSame($payment, $handler->start('order-3001', 'internal', '12.50', 'USD'));
    }
}
