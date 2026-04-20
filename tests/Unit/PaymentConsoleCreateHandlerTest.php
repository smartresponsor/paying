<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\Payment;
use App\Paying\Service\PaymentConsoleCreateHandler;
use App\Paying\ServiceInterface\PaymentServiceInterface;
use App\Paying\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment console create handler scenario within the payment unit test surface.
 */
final class PaymentConsoleCreateHandlerTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that create delegates to payment service.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCreateDelegatesToPaymentService(): void
    {
        $payment = new Payment(new Ulid(), PaymentStatus::new, '10.00', 'USD');

        $service = $this->createMock(PaymentServiceInterface::class);
        $service
            ->expects(self::once())
            ->method('create')
            ->with('order-1', 1000, 'USD')
            ->willReturn($payment);

        $handler = new PaymentConsoleCreateHandler($service);

        self::assertSame($payment, $handler->create('order-1', 1000, 'USD'));
    }
}
