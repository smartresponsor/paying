<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\Service\ProviderGuard;
use App\Service\ProviderRouter;
use App\ServiceInterface\CircuitBreakerInterface;
use App\ServiceInterface\MetricInterface;
use App\ServiceInterface\PaymentProviderInterface;
use App\ServiceInterface\RetryExecutorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the provider guard scenario within the payment unit test surface.
 */
final class ProviderGuardTest extends TestCase
{
    /**
     * Verifies that start throws when circuit is open.
     */
    public function testStartThrowsWhenCircuitIsOpen(): void
    {
        $breaker = $this->createMock(CircuitBreakerInterface::class);
        $breaker->method('isOpen')->willReturn(true);
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects(self::once())->method('incProviderFailure')->with('stripe', 'start');
        $metric->expects(self::once())->method('observeProviderDuration')->with('stripe', 'start', self::isType('float'));

        $guard = new ProviderGuard(new ProviderRouter([]), $this->createMock(RetryExecutorInterface::class), $breaker, $metric);

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
            public function start(Payment $payment, array $context = []): array
            {
                return ['providerRef' => 'ok'];
            }

            /**
             * Provides the finalize behavior required by this test scenario.
             */
            public function finalize(Ulid $id, array $payload = []): Payment
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the refund behavior required by this test scenario.
             */
            public function refund(Ulid $id, string $amount): Payment
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the reconcile behavior required by this test scenario.
             */
            public function reconcile(Ulid $id): Payment
            {
                throw new \RuntimeException();
            }
        };

        $router = new ProviderRouter(['stripe' => $provider]);

        /** @var RetryExecutorInterface&MockObject $retry */
        $retry = $this->createMock(RetryExecutorInterface::class);
        $retry->method('execute')->willReturnCallback(fn ($fn) => $fn());

        /** @var CircuitBreakerInterface&MockObject $breaker */
        $breaker = $this->createMock(CircuitBreakerInterface::class);
        $breaker->method('isOpen')->willReturn(false);
        $breaker->expects(self::once())->method('recordSuccess');
        /** @var MetricInterface&MockObject $metric */
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects(self::once())->method('incProviderSuccess')->with('stripe', 'start');
        $metric->expects(self::once())->method('observeProviderDuration')->with('stripe', 'start', self::isType('float'));

        $guard = new ProviderGuard($router, $retry, $breaker, $metric);

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
            public function start(Payment $payment, array $context = []): array
            {
                throw new \RuntimeException('boom');
            }

            /**
             * Provides the finalize behavior required by this test scenario.
             */
            public function finalize(Ulid $id, array $payload = []): Payment
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the refund behavior required by this test scenario.
             */
            public function refund(Ulid $id, string $amount): Payment
            {
                throw new \RuntimeException();
            }

            /**
             * Provides the reconcile behavior required by this test scenario.
             */
            public function reconcile(Ulid $id): Payment
            {
                throw new \RuntimeException();
            }
        };

        $router = new ProviderRouter(['stripe' => $provider]);

        /** @var RetryExecutorInterface&MockObject $retry */
        $retry = $this->createMock(RetryExecutorInterface::class);
        $retry->method('execute')->willReturnCallback(fn ($fn) => $fn());

        /** @var CircuitBreakerInterface&MockObject $breaker */
        $breaker = $this->createMock(CircuitBreakerInterface::class);
        $breaker->method('isOpen')->willReturn(false);
        $breaker->expects(self::once())->method('recordFailure');
        /** @var MetricInterface&MockObject $metric */
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects(self::once())->method('incProviderFailure')->with('stripe', 'start');
        $metric->expects(self::once())->method('observeProviderDuration')->with('stripe', 'start', self::isType('float'));

        $guard = new ProviderGuard($router, $retry, $breaker, $metric);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');

        $guard->start('stripe', $this->dummyPayment());
    }

    private function dummyPayment(): Payment
    {
        return new Payment(new Ulid('01ARZ3NDEKTSV4RRFFQ69G5FZZ'), \App\ValueObject\PaymentStatus::new, '10.00', 'USD');
    }
}
