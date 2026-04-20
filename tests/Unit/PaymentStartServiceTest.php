<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\Payment;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\Service\PaymentStartService;
use App\Paying\ServiceInterface\ProviderGuardInterface;
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
            public ?Payment $saved = null;
            public int $saveCount = 0;

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(Payment $payment): void
            {
                $this->saved = $payment;
                ++$this->saveCount;
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

        $guard = new class implements ProviderGuardInterface {
            /** @var array<string, mixed> */
            public array $receivedContext = [];

            /**
             * Provides the start behavior required by this test scenario.
             */
            public function start(string $provider, Payment $payment, array $context = []): array
            {
                $this->receivedContext = $context;

                return ['providerRef' => 'provider-ref-123'];
            }

            /**
             * Provides the finalize behavior required by this test scenario.
             */
            public function finalize(string $provider, Ulid $id, array $payload = []): Payment
            {
                throw new \RuntimeException('not used');
            }

            /**
             * Provides the refund behavior required by this test scenario.
             */
            public function refund(string $provider, Ulid $id, string $amount): Payment
            {
                throw new \RuntimeException('not used');
            }

            /**
             * Provides the reconcile behavior required by this test scenario.
             */
            public function reconcile(string $provider, Ulid $id): Payment
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
        self::assertSame((string) $payment->id(), $guard->receivedContext['idempotencyKey']);
        self::assertSame((string) $payment->id(), $guard->receivedContext['projectId']);
    }

    /**
     * Verifies that start marks payment failed on provider error.
     */
    public function testStartMarksPaymentFailedOnProviderError(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public int $saveCount = 0;
            public ?Payment $last = null;

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(Payment $payment): void
            {
                ++$this->saveCount;
                $this->last = $payment;
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
        $guard = $this->createMock(ProviderGuardInterface::class);

        $repo->expects(self::never())->method('save');
        $guard->expects(self::never())->method('start');

        $service = new PaymentStartService($guard, $repo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be in decimal format like 10.00.');

        $service->start('order-1001', 'internal', '10', 'USD');
    }
}
