<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\RepositoryInterface\PaymentOutboxRepositoryInterface;
use App\Paying\Service\PaymentOutboxPublisher;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Exercises the outbox publisher enqueue scenario within the payment unit test surface.
 */
final class PaymentOutboxPublisherEnqueueTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \JsonException
     */
    /**
     * Verifies that enqueue writes unified payment outbox message table.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testEnqueueWritesUnifiedPaymentOutboxMessageTable(): void
    {
        $outbox = $this->createMock(PaymentOutboxRepositoryInterface::class);
        $outbox->expects(self::once())
            ->method('enqueue')
            ->with('payment.captured', ['paymentId' => '01TESTPAYMENT']);

        $publisher = new PaymentOutboxPublisher($outbox, new NullLogger());
        $publisher->enqueue('payment.captured', ['paymentId' => '01TESTPAYMENT']);
    }
}
