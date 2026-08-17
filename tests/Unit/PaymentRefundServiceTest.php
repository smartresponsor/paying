<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\Service\PaymentNotFoundException;
use App\Paying\Service\PaymentRefundService;
use App\Paying\ServiceInterface\PaymentProviderGuardInterface;
use App\Paying\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the refund service scenario within the payment unit test surface.
 */
final class PaymentRefundServiceTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that refund throws typed not found exception.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRefundThrowsTypedNotFoundException(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(PaymentEntity $payment): void
            {
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?PaymentEntity
            {
                return null;
            }

            /**
             * Implements the find by order id behavior required by the local test double used in this scenario.
             */
            public function findByOrderId(string $orderId): ?PaymentEntity
            {
                return null;
            }

            /**
             * Implements the list recent behavior required by the local test double used in this scenario.
             */
            public function listRecent(int $limit = 10): array
            {
                return [];
            }

            /**
             * Implements the list ids by statuses behavior required by the local test double used in this scenario.
             */
            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }

            public function listUpdatedAfter(\DateTimeImmutable $updatedAfter, int $limit = 500): array
            {
                throw new \LogicException('Test repository stub method is not configured: listUpdatedAfter');
            }

            public function listAllOrderedByUpdatedAt(int $limit = 1000, int $offset = 0): array
            {
                throw new \LogicException('Test repository stub method is not configured: listAllOrderedByUpdatedAt');
            }

            public function maxUpdatedAt(): ?string
            {
                throw new \LogicException('Test repository stub method is not configured: maxUpdatedAt');
            }

            public function countByStatusSince(\DateTimeImmutable $since): array
            {
                throw new \LogicException('Test repository stub method is not configured: countByStatusSince');
            }
        };

        $guard = $this->createMock(PaymentProviderGuardInterface::class);
        $guard->expects(self::never())->method('refund');

        $service = new PaymentRefundService($guard, $repo);

        $this->expectException(PaymentNotFoundException::class);
        $service->refund(new Ulid(), '10.00');
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that refund syncs resolved payment and persists.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRefundSyncsResolvedPaymentAndPersists(): void
    {
        $existing = new PaymentEntity(new Ulid(), PaymentStatus::processing, '10.00', 'USD');
        $resolved = new PaymentEntity(new Ulid(), PaymentStatus::refunded, '10.00', 'USD');

        $repo = new class($existing) implements PaymentRepositoryInterface {
            public ?PaymentEntity $saved = null;

            public function __construct(private readonly PaymentEntity $existing)
            {
            }

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(PaymentEntity $payment): void
            {
                $this->saved = $payment;
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?PaymentEntity
            {
                return $this->existing;
            }

            /**
             * Implements the find by order id behavior required by the local test double used in this scenario.
             */
            public function findByOrderId(string $orderId): ?PaymentEntity
            {
                return null;
            }

            /**
             * Implements the list recent behavior required by the local test double used in this scenario.
             */
            public function listRecent(int $limit = 10): array
            {
                return [];
            }

            /**
             * Implements the list ids by statuses behavior required by the local test double used in this scenario.
             */
            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }

            public function listUpdatedAfter(\DateTimeImmutable $updatedAfter, int $limit = 500): array
            {
                throw new \LogicException('Test repository stub method is not configured: listUpdatedAfter');
            }

            public function listAllOrderedByUpdatedAt(int $limit = 1000, int $offset = 0): array
            {
                throw new \LogicException('Test repository stub method is not configured: listAllOrderedByUpdatedAt');
            }

            public function maxUpdatedAt(): ?string
            {
                throw new \LogicException('Test repository stub method is not configured: maxUpdatedAt');
            }

            public function countByStatusSince(\DateTimeImmutable $since): array
            {
                throw new \LogicException('Test repository stub method is not configured: countByStatusSince');
            }
        };

        $guard = $this->createMock(PaymentProviderGuardInterface::class);
        $guard
            ->expects(self::once())
            ->method('refund')
            ->with('internal', self::isInstanceOf(Ulid::class), '10.00')
            ->willReturn($resolved);

        $service = new PaymentRefundService($guard, $repo);
        $result = $service->refund(new Ulid(), '10.00');

        self::assertSame($existing, $result);
        self::assertSame(PaymentStatus::refunded, $result->status());
        self::assertSame($existing, $repo->saved);
    }
}
