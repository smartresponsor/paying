<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentOutboxMessageEntity;
use App\Paying\Infrastructure\PaymentOutboxPublisher;
use Doctrine\ORM\EntityManagerInterface;
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
        $connection = $this->createMock(EntityManagerInterface::class);
        $connection->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $connection->expects(self::once())
            ->method('persist')
            ->with(self::callback(static fn (mixed $entity): bool => $entity instanceof PaymentOutboxMessageEntity));
        $connection->expects(self::once())
            ->method('flush');

        $publisher = new PaymentOutboxPublisher($connection, new NullLogger());
        $publisher->enqueue('payment.captured', ['paymentId' => '01TESTPAYMENT']);
    }
}
