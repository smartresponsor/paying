<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Service\PaymentProviderGuard;
use App\Paying\Service\PaymentProviderRouter;
use App\Paying\ServiceInterface\PaymentCircuitBreakerInterface;
use App\Paying\ServiceInterface\PaymentMetricInterface;
use App\Paying\ServiceInterface\PaymentProviderInterface;
use App\Paying\ServiceInterface\PaymentRetryExecutorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the provider guard scenario within the payment unit test surface.
 */
final class PaymentProviderGuardTest extends TestCase
{
    /**
     * Verifies that start throws when circuit is open.
     */
    public function testStartThrowsWhenCircuitIsOpen(): void
    {
        $breaker = $this->createMock(PaymentCircuitBreakerInterface::class);
        $breaker->method('isOpen')->willReturn(true);
        $metric = $this->createMock(PaymentMetricInterface::class);
        $metric->expects(self::once())->method('incProviderFailure')->with('stripe', 'start');
        $metric->expects(self::never())->method('observeProviderDuration');

        $guard = new PaymentProviderGuard(new PaymentProviderRouter([]), $this->createMock(PaymentRetryExecutorInterface::class), $breaker, $metric);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Circuit open');

        $guard->start('stripe', $this->dummyPayment());
    }

    /**
     * Verifies that start records success.
     */
    public function testStartRecordsSuccess(): void
    {
        $provider = new class implements PaymentProviderInterface {
            /**
             * Provides the start behavior required by this test scenario.
             */
            public function start(PaymentEntity $payment, array $context = []): array
            {
                return ['providerRef' => 'ok'];
            }

            /**
             * Provides the finalize behavior required by this test scenario.
             */
            public function finalize(Ulid $id, array $payload = []): PaymentEntity
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the refund behavior required by this test scenario.
             */
            public function refund(Ulid $id, string $amount): PaymentEntity
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the reconcile behavior required by this test scenario.
             */
            public function reconcile(Ulid $id): PaymentEntity
            {
                throw new \RuntimeException();
            }
        };

        $router = new PaymentProviderRouter(['stripe' => $provider]);

        /** @var PaymentRetryExecutorInterface&MockObject $retry */
        $retry = $this->createMock(PaymentRetryExecutorInterface::class);
        $retry->method('execute')->willReturnCallback(fn ($fn) => $fn());

        /** @var PaymentCircuitBreakerInterface&MockObject $breaker */
        $breaker = $this->createMock(PaymentCircuitBreakerInterface::class);
        $breaker->method('isOpen')->willReturn(false);
        $breaker->expects(self::once())->method('recordSuccess');
        /** @var PaymentMetricInterface&MockObject $metric */
        $metric = $this->createMock(PaymentMetricInterface::class);
        $metric->expects(self::once())->method('incProviderSuccess')->with('stripe', 'start');
        $metric->expects(self::once())->method('observeProviderDuration')->with('stripe', 'start', self::isFloat());

        $guard = new PaymentProviderGuard($router, $retry, $breaker, $metric);

        $result = $guard->start('stripe', $this->dummyPayment());

        self::assertSame('ok', $result['providerRef']);
    }

    /**
     * Verifies that start records failure and rethrows.
     */
    public function testStartRecordsFailureAndRethrows(): void
    {
        $provider = new class implements PaymentProviderInterface {
            /**
             * Provides the start behavior required by this test scenario.
             */
            public function start(PaymentEntity $payment, array $context = []): array
            {
                throw new \RuntimeException('boom');
            }

            /**
             * Provides the finalize behavior required by this test scenario.
             */
            public function finalize(Ulid $id, array $payload = []): PaymentEntity
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the refund behavior required by this test scenario.
             */
            public function refund(Ulid $id, string $amount): PaymentEntity
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the reconcile behavior required by this test scenario.
             */
            public function reconcile(Ulid $id): PaymentEntity
            {
                throw new \RuntimeException();
            }
        };

        $router = new PaymentProviderRouter(['stripe' => $provider]);

        /** @var PaymentRetryExecutorInterface&MockObject $retry */
        $retry = $this->createMock(PaymentRetryExecutorInterface::class);
        $retry->method('execute')->willReturnCallback(fn ($fn) => $fn());

        /** @var PaymentCircuitBreakerInterface&MockObject $breaker */
        $breaker = $this->createMock(PaymentCircuitBreakerInterface::class);
        $breaker->method('isOpen')->willReturn(false);
        $breaker->expects(self::once())->method('recordFailure');
        /** @var PaymentMetricInterface&MockObject $metric */
        $metric = $this->createMock(PaymentMetricInterface::class);
        $metric->expects(self::once())->method('incProviderFailure')->with('stripe', 'start');
        $metric->expects(self::once())->method('observeProviderDuration')->with('stripe', 'start', self::isFloat());

        $guard = new PaymentProviderGuard($router, $retry, $breaker, $metric);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');

        $guard->start('stripe', $this->dummyPayment());
    }

    private function dummyPayment(): PaymentEntity
    {
        return new PaymentEntity(new Ulid('01ARZ3NDEKTSV4RRFFQ69G5FZZ'), \App\Paying\ValueObject\PaymentStatus::new, '10.00', 'USD');
    }
}
