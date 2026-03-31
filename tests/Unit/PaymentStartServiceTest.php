<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentStartService;
use App\ServiceInterface\ProviderGuardInterface;
use PHPUnit\Framework\MockObject\MockObject;
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

            public function find(string $id): ?Payment { return null; }
            public function listRecent(int $limit = 10): array { return []; }
            public function listIdsByStatuses(array $statuses, int $limit = 100): array { return []; }
        };

        $guard = new class implements ProviderGuardInterface {
            public function start(string $provider, Payment $payment, array $context = []): array { return ['providerRef' => 'provider-ref-123']; }
            public function finalize(string $provider, Ulid $id, array $payload = []): Payment { throw new \RuntimeException(); }
            public function refund(string $provider, Ulid $id, string $amount): Payment { throw new \RuntimeException(); }
            public function reconcile(string $provider, Ulid $id): Payment { throw new \RuntimeException(); }
        };

        $service = new PaymentStartService($guard, $repo);
        $started = $service->start('internal', '10.00', 'usd');

        self::assertSame(2, $repo->saveCount);
        self::assertSame('processing', $started->payment->status()->value);
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

            public function find(string $id): ?Payment { return null; }
            public function listRecent(int $limit = 10): array { return []; }
            public function listIdsByStatuses(array $statuses, int $limit = 100): array { return []; }
        };

        /** @var ProviderGuardInterface&MockObject $guard */
        $guard = $this->createMock(ProviderGuardInterface::class);
        $guard->method('start')->willThrowException(new \RuntimeException('fail'));

        $service = new PaymentStartService($guard, $repo);

        $this->expectException(\RuntimeException::class);

        try {
            $service->start('stripe', '10.00', 'USD');
        } finally {
            self::assertSame(2, $repo->saveCount);
            self::assertSame('failed', $repo->last?->status()->value);
        }
    }
}
