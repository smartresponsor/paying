<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\CircuitBreakerInterface;
use App\ServiceInterface\MetricInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\ProviderRouterInterface;
use App\ServiceInterface\RetryExecutorInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the provider guard service used by the payment lifecycle and operator-facing flows.
 */
readonly class ProviderGuard implements ProviderGuardInterface
{
    public function __construct(
        private ProviderRouterInterface $router,
        private RetryExecutorInterface $retry,
        private CircuitBreakerInterface $breaker,
        private MetricInterface $metric,
    ) {
    }

    /**
     * Executes the start operation for the current payment workflow.
     *
     * @param string               $provider
     * @param Payment              $payment
     * @param array<string, mixed> $context
     *
     * @return array{provider: string, paymentId: string, accepted?: bool, status?: string, providerRef?: string|null, checkoutUrl?: string, result?: array<string, mixed>}
     *
     * @throws \Throwable
     */
    public function start(string $provider, Payment $payment, array $context = []): array
    {
        return $this->measure('start', $provider, function () use ($provider, $payment, $context) {
            return $this->router->for($provider)->start($payment, $context);
        });
    }

    /**
     * Executes the finalize operation for the current payment workflow.
     */
    public function finalize(string $provider, Ulid $id, array $payload = []): Payment
    {
        return $this->measure('finalize', $provider, function () use ($provider, $id, $payload) {
            return $this->router->for($provider)->finalize($id, $payload);
        });
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(string $provider, Ulid $id, string $amount): Payment
    {
        return $this->measure('refund', $provider, function () use ($provider, $id, $amount) {
            return $this->router->for($provider)->refund($id, $amount);
        });
    }

    /**
     * Executes the reconcile operation for the current payment workflow.
     */
    public function reconcile(string $provider, Ulid $id): Payment
    {
        return $this->measure('reconcile', $provider, function () use ($provider, $id) {
            return $this->router->for($provider)->reconcile($id);
        });
    }

    private function measure(string $operation, string $provider, callable $fn): mixed
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            $this->metric->incProviderFailure($provider, $operation);
            throw new \RuntimeException('Circuit open for '.$provider);
        }

        $start = microtime(true);

        try {
            $result = $this->retry->execute($fn);

            $this->breaker->recordSuccess($key);
            $this->metric->incProviderSuccess($provider, $operation);

            return $result;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            $this->metric->incProviderFailure($provider, $operation);
            throw $e;
        } finally {
            $duration = (microtime(true) - $start) * 1000;
            $this->metric->observeProviderDuration($provider, $operation, $duration);
        }
    }
}
