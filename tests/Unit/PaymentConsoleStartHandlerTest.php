<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\Service\PaymentConsoleStartHandler;
use App\Service\PaymentStartResult;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleStartHandlerTest extends TestCase
{
    public function testStartReturnsPaymentFromStartService(): void
    {
        $payment = new Payment(new Ulid(), PaymentStatus::processing, '12.50', 'USD');

        $startService = $this->createMock(PaymentStartServiceInterface::class);
        $startService
            ->expects(self::once())
            ->method('start')
            ->with('internal', '12.50', 'USD', '', 'payment-console')
            ->willReturn(new PaymentStartResult($payment, null, []));

        $handler = new PaymentConsoleStartHandler($startService);

        self::assertSame($payment, $handler->start('internal', '12.50', 'USD'));
    }

}
