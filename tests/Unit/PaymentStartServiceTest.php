<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentStartService;
use App\ServiceInterface\ProviderGuardInterface;
use PHPUnit\Framework\MockObject\Exception;
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

            /**
             * @return string[]
             */
            public function start(string $provider, Payment $payment, array $context = []): array
            {
                $this->receivedContext = $context;

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
        $started = $service->start('internal', '10.00', 'usd', '', 'payment-console');
        $payment = $started->payment;

        self::assertSame(2, $repo->saveCount);
        self::assertSame($payment, $repo->saved);
        self::assertSame('processing', $payment->status()->value);
        self::assertSame('provider-ref-123', $started->providerRef);
        self::assertSame('USD', $payment->currency());
        self::assertSame('payment-console', $guard->receivedContext['origin']);
        self::assertArrayHasKey('idempotencyKey', $guard->receivedContext);
    }

    public function testStartRejectsInvalidAmountFormat(): void
    {
        try {
            $repo = $this->createMock(PaymentRepositoryInterface::class);
        } catch (Exception $e) {
        }
        try {
            $guard = $this->createMock(ProviderGuardInterface::class);
        } catch (Exception $e) {
        }

        $repo->expects(self::never())->method('save');
        $guard->expects(self::never())->method('start');

        $service = new PaymentStartService($guard, $repo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be in decimal format like 10.00.');

        $service->start('internal', '10', 'USD');
    }
}
