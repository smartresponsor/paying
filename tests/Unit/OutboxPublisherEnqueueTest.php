<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Infrastructure\OutboxPublisher;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Exercises the outbox publisher enqueue scenario within the payment unit test surface.
 */
final class OutboxPublisherEnqueueTest extends TestCase
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
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('insert')
            ->with(
                'payment_outbox_message',
                self::callback(static function (array $data): bool {
                    return isset($data['id'], $data['type'], $data['payload'], $data['occurred_at'], $data['status'], $data['attempts'], $data['routing_key'])
                        && 'payment.captured' === $data['type']
                        && 'pending' === $data['status']
                        && 0 === $data['attempts']
                        && 'payment.captured' === $data['routing_key'];
                }),
            );

        $publisher = new OutboxPublisher($connection, new NullLogger());
        $publisher->enqueue('payment.captured', ['paymentId' => '01TESTPAYMENT']);
    }
}
