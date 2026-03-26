<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\Service\PaymentConsoleCreateHandler;
use App\ServiceInterface\PaymentServiceInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleCreateHandlerTest extends TestCase
{
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
