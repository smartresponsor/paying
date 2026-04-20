<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit\Reconciliation;

use App\Paying\Entity\Payment;
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
            public function save(Payment $payment): void
            {
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?Payment
            {
                return null;
            }

            /**
             * Implements the find by order id behavior required by the local test double used in this scenario.
             */
            public function findByOrderId(string $orderId): ?Payment
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
        };
        $em = $this->createMock(EntityManagerInterface::class);
        $svc = new PaymentReconciliationService($repo, $em);
        $svc->onFailed('missing', 'declined', 'Card declined');

        self::addToAssertionCount(1);
    }
}
