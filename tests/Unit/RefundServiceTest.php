<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentNotFoundException;
use App\Service\RefundService;
use App\ServiceInterface\ProviderGuardInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the refund service scenario within the payment unit test surface.
 */
final class RefundServiceTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that refund throws typed not found exception.
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRefundThrowsTypedNotFoundException(): void
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

        $guard = $this->createMock(ProviderGuardInterface::class);
        $guard->expects(self::never())->method('refund');

        $service = new RefundService($guard, $repo);

        $this->expectException(PaymentNotFoundException::class);
        $service->refund(new Ulid(), '10.00');
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that refund syncs resolved payment and persists.
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRefundSyncsResolvedPaymentAndPersists(): void
    {
        $existing = new Payment(new Ulid(), PaymentStatus::processing, '10.00', 'USD');
        $resolved = new Payment(new Ulid(), PaymentStatus::refunded, '10.00', 'USD');

        $repo = new class($existing) implements PaymentRepositoryInterface {
            public ?Payment $saved = null;

            public function __construct(private readonly Payment $existing)
            {
            }

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(Payment $payment): void
            {
                $this->saved = $payment;
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?Payment
            {
                return $this->existing;
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

        $guard = $this->createMock(ProviderGuardInterface::class);
        $guard
            ->expects(self::once())
            ->method('refund')
            ->with('internal', self::isInstanceOf(Ulid::class), '10.00')
            ->willReturn($resolved);

        $service = new RefundService($guard, $repo);
        $result = $service->refund(new Ulid(), '10.00');

        self::assertSame($existing, $result);
        self::assertSame(PaymentStatus::refunded, $result->status());
        self::assertSame($existing, $repo->saved);
    }
}
