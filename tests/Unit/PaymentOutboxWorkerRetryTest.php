<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\Business\PaymentOutboxMessageEntity;
use App\Paying\RepositoryInterface\PaymentOutboxRepositoryInterface;
use App\Paying\Service\PaymentOutboxWorker;
use App\Paying\ServiceInterface\PaymentOutboxPublisherInterface;
use App\Paying\ServiceInterface\PaymentPublisherTransportInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Exercises the outbox worker retry scenario within the payment unit test surface.
 */
final class PaymentOutboxWorkerRetryTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that run marks failed before dlq threshold.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRunMarksFailedBeforeDlqThreshold(): void
    {
        $outbox = $this->createMock(PaymentOutboxRepositoryInterface::class);
        $transport = $this->createMock(PaymentPublisherTransportInterface::class);
        $publisher = $this->createMock(PaymentOutboxPublisherInterface::class);
        $message = new PaymentOutboxMessageEntity(
            '01TESTOUTBOX00000000000000',
            'payment.failed',
            ['paymentId' => '01TESTPAYMENT'],
            'payment.failed',
        );

        $outbox->expects(self::once())
            ->method('listProcessable')
            ->with(10, false)
            ->willReturn([$message]);

        $transport->expects(self::once())
            ->method('publish')
            ->with('payment.failed', self::callback(static fn (mixed $payload): bool => is_array($payload)))
            ->willThrowException(new \RuntimeException('broker unavailable'));

        $outbox->expects(self::once())
            ->method('flush');

        $publisher->expects(self::never())->method('moveToDlq');

        $worker = new PaymentOutboxWorker($outbox, $transport, $publisher, new NullLogger());
        self::assertSame(0, $worker->run(10));
    }
}
