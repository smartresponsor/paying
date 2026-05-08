<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentOutboxMessageEntity;
use App\Paying\Infrastructure\PaymentOutboxWorker;
use App\Paying\InfrastructureInterface\PaymentOutboxPublisherInterface;
use App\Paying\InfrastructureInterface\PaymentPublisherTransportInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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
        $connection = $this->createMock(EntityManagerInterface::class);
        $queryBuilder = $this->createStub(QueryBuilder::class);
        $query = $this->createStub(Query::class);
        $transport = $this->createMock(PaymentPublisherTransportInterface::class);
        $publisher = $this->createMock(PaymentOutboxPublisherInterface::class);
        $message = new PaymentOutboxMessageEntity(
            '01TESTOUTBOX00000000000000',
            'payment.failed',
            ['paymentId' => '01TESTPAYMENT'],
            'payment.failed',
        );

        $connection->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('addOrderBy')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getResult')->willReturn([$message]);

        $transport->expects(self::once())
            ->method('publish')
            ->with('payment.failed', self::callback(static fn (mixed $payload): bool => is_array($payload)))
            ->willThrowException(new \RuntimeException('broker unavailable'));

        $connection->expects(self::once())
            ->method('flush');

        $publisher->expects(self::never())->method('moveToDlq');

        $worker = new PaymentOutboxWorker($connection, $transport, $publisher, new NullLogger());
        self::assertSame(0, $worker->run(10));
    }
}
