<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit\Reconciliation;

use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\Service\Reconciliation\PaymentReconciliationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the payment reconciliation service scenario within the payment reconciliation test surface.
 */
final class PaymentReconciliationServiceTest extends TestCase
{
    /**
     * Verifies that on failed does not throw when payment missing.
     */
    public function testOnFailedDoesNotThrowWhenPaymentMissing(): void
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
        $em = $this->createMock(EntityManagerInterface::class);
        $svc = new PaymentReconciliationService($repo, $em);
        $svc->onFailed('missing', 'declined', 'Card declined');

        self::addToAssertionCount(1);
    }
}
