<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Service\PaymentConsoleRefundHandler;
use App\Paying\Service\PaymentNotFoundException;
use App\Paying\ServiceInterface\RefundServiceInterface;
use App\Paying\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment console refund handler scenario within the payment unit test surface.
 */
final class PaymentConsoleRefundHandlerTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that refund returns payment on success.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRefundReturnsPaymentOnSuccess(): void
    {
        $payment = new PaymentEntity(new Ulid(), PaymentStatus::refunded, '10.00', 'USD');

        $refundService = $this->createMock(RefundServiceInterface::class);
        $refundService
            ->expects(self::once())
            ->method('refund')
            ->with(self::isInstanceOf(Ulid::class), '10.00', 'internal')
            ->willReturn($payment);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $handler = new PaymentConsoleRefundHandler($refundService, $logger);

        self::assertSame($payment, $handler->refund((string) new Ulid(), '10.00', 'internal'));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that refund returns null and logs on failure.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRefundReturnsNullAndLogsOnFailure(): void
    {
        $refundService = $this->createMock(RefundServiceInterface::class);
        $refundService
            ->expects(self::once())
            ->method('refund')
            ->willThrowException(PaymentNotFoundException::byId('test-id'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                'PaymentEntity console refund failed.',
                self::arrayHasKey('payment_id'),
            );

        $handler = new PaymentConsoleRefundHandler($refundService, $logger);

        self::assertNull($handler->refund((string) new Ulid(), '10.00', 'internal'));
    }
}
