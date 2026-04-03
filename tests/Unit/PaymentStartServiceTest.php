<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentStartService;
use App\ServiceInterface\ProviderGuardInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentStartServiceTest extends TestCase
{
    public function testStartPersistsPaymentAndUpdatesStatus(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public ?Payment $saved = null;
            public int $saveCount = 0;

            public function save(Payment $payment): void
            {
                $this->saved = $payment;
                ++$this->saveCount;
            }

            public function find(string $id): ?Payment
            {
                return null;
            }

            public function findByOrderId(string $orderId): ?Payment
            {
                return null;
            }

            public function listRecent(int $limit = 10): array
            {
                return [];
            }

            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }
        };

        $guard = new class implements ProviderGuardInterface {
            /** @var array<string, mixed> */
            public array $receivedContext = [];

            public function start(string $provider, Payment $payment, array $context = []): array
            {
                $this->receivedContext = $context;

                return ['providerRef' => 'provider-ref-123'];
            }

            public function finalize(string $provider, Ulid $id, array $payload = []): Payment
            {
                throw new \RuntimeException('not used');
            }

            public function refund(string $provider, Ulid $id, string $amount): Payment
            {
                throw new \RuntimeException('not used');
            }

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

    public function testStartMarksPaymentFailedOnProviderError(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public int $saveCount = 0;
            public ?Payment $last = null;

            public function save(Payment $payment): void
            {
                ++$this->saveCount;
                $this->last = $payment;
            }

            public function find(string $id): ?Payment
            {
                return null;
            }

            public function findByOrderId(string $orderId): ?Payment
            {
                return null;
            }

            public function listRecent(int $limit = 10): array
            {
                return [];
            }

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
