<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Payment\Unit;

use App\Infrastructure\Payment\OutboxPublisherInterface;
use App\Infrastructure\Payment\OutboxWorker;
use App\Infrastructure\Payment\PublisherTransportInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

final class OutboxWorkerRetryTest extends TestCase
{
    public function testRunMarksFailedBeforeDlqThreshold(): void
    {
        $connection = $this->createMock(Connection::class);
        $transport = $this->createMock(PublisherTransportInterface::class);
        $publisher = $this->createMock(OutboxPublisherInterface::class);

        $connection->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn([[
                'id' => '01TESTOUTBOX00000000000000',
                'type' => 'payment.failed',
                'routing_key' => 'payment.failed',
                'payload' => json_encode(['paymentId' => '01TESTPAYMENT']),
                'status' => 'pending',
                'attempts' => 0,
            ]]);

        $transport->expects(self::once())
            ->method('publish')
            ->with('payment.failed', self::callback(static fn (mixed $payload): bool => is_array($payload)))
            ->willThrowException(new \RuntimeException('broker unavailable'));

        $connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                self::callback(static fn (string $sql): bool => str_contains($sql, 'UPDATE payment_outbox_message SET status = :status')),
                self::callback(static function (array $params): bool {
                    return 'failed' === $params['status']
                        && 1 === $params['attempts']
                        && '01TESTOUTBOX00000000000000' === $params['id']
                        && is_string($params['lastError']);
                }),
            );

        $publisher->expects(self::never())->method('moveToDlq');

        $worker = new OutboxWorker($connection, $transport, $publisher);
        self::assertSame(0, $worker->run(10, false));
    }
}
