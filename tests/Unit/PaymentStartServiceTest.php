<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\Service\PaymentStartService;
use App\Paying\ServiceInterface\PaymentProviderGuardInterface;
use App\Paying\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment start service scenario within the payment unit test surface.
 */
final class PaymentStartServiceTest extends TestCase
{
    /**
     * Verifies that start persists payment and updates status.
     */
    public function testStartPersistsPaymentAndUpdatesStatus(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public ?PaymentEntity $saved = null;
            public int $saveCount = 0;

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(PaymentEntity $payment): void
            {
                $this->saved = $payment;
                ++$this->saveCount;
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

        $guard = new class implements PaymentProviderGuardInterface {
            /** @var array<string, mixed> */
            public array $receivedContext = [];

            /**
             * Provides the start behavior required by this test scenario.
             */
            public function start(string $provider, PaymentEntity $payment, array $context = []): array
            {
                $this->receivedContext = $context;

                return ['providerRef' => 'provider-ref-123'];
            }

            /**
             * Provides the finalize behavior required by this test scenario.
             */
            public function finalize(string $provider, Ulid $id, array $payload = []): PaymentEntity
            {
                throw new \RuntimeException('not used');
            }

            /**
             * Provides the refund behavior required by this test scenario.
             */
            public function refund(string $provider, Ulid $id, string $amount): PaymentEntity
            {
                throw new \RuntimeException('not used');
            }

            /**
             * Provides the reconcile behavior required by this test scenario.
             */
            public function reconcile(string $provider, Ulid $id): PaymentEntity
            {
                throw new \RuntimeException('not used');
            }
        };

        $service = new PaymentStartService($guard, $repo);
        $started = $service->start('order-1001', 'internal', '10.00', 'usd', '', 'payment-console');
        $payment = $started->payment;

        self::assertSame(2, $repo->saveCount);
        self::assertSame($payment, $repo->saved);
        self::assertSame('processing', $payment->status()->value);
        self::assertSame('provider-ref-123', $started->providerRef);
        self::assertSame('USD', $payment->currency());
        self::assertSame('order-1001', $payment->orderId());
        self::assertSame('payment-console', $guard->receivedContext['origin']);
        self::assertSame($payment->slug(), $guard->receivedContext['idempotencyKey']);
        self::assertSame($payment->slug(), $guard->receivedContext['projectId']);
    }

    /**
     * Verifies that start marks payment failed on provider error.
     */
    public function testStartMarksPaymentFailedOnProviderError(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public int $saveCount = 0;
            public ?PaymentEntity $last = null;

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(PaymentEntity $payment): void
            {
                ++$this->saveCount;
                $this->last = $payment;
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
        $guard->method('start')->willThrowException(new \RuntimeException('fail'));

        $service = new PaymentStartService($guard, $repo);

        try {
            $service->start('order-1001', 'internal', '10.00', 'usd', '', 'payment-console');
            self::fail('Expected provider exception to be rethrown.');
        } catch (\RuntimeException $exception) {
            self::assertSame('fail', $exception->getMessage());
        }

        self::assertSame(2, $repo->saveCount);
        self::assertNotNull($repo->last);
        self::assertSame(PaymentStatus::failed, $repo->last->status());
    }

    /**
     * Verifies that start rejects invalid amount format.
     */
    public function testStartRejectsInvalidAmountFormat(): void
    {
        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $guard = $this->createMock(PaymentProviderGuardInterface::class);

        $repo->expects(self::never())->method('save');
        $guard->expects(self::never())->method('start');

        $service = new PaymentStartService($guard, $repo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be in decimal format like 10.00.');

        $service->start('order-1001', 'internal', '10', 'USD');
    }
}
