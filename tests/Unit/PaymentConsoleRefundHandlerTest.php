<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\Service\PaymentConsoleRefundHandler;
use App\Service\PaymentNotFoundException;
use App\ServiceInterface\RefundServiceInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleRefundHandlerTest extends TestCase
{
    public function testRefundReturnsPaymentOnSuccess(): void
    {
        $payment = new Payment(new Ulid(), PaymentStatus::refunded, '10.00', 'USD');

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
                'Payment console refund failed.',
                self::arrayHasKey('payment_id'),
            );

        $handler = new PaymentConsoleRefundHandler($refundService, $logger);

        self::assertNull($handler->refund((string) new Ulid(), '10.00', 'internal'));
    }
}
