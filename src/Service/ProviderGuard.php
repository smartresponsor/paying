<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\CircuitBreakerInterface;
use App\ServiceInterface\MetricInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RetryExecutorInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Guard layer that wraps provider execution with retry, circuit breaker,
 * and metrics instrumentation.
 *
 * This class is responsible for operational resilience and observability,
 * not for business logic.
 */
readonly class ProviderGuard implements ProviderGuardInterface
{
    public function __construct(
        private ProviderRouter $router,
        private RetryExecutorInterface $retry,
        private CircuitBreakerInterface $breaker,
        private MetricInterface $metric,
    ) {}

    /**
     * Executes a start operation with resilience mechanisms.
     */
    public function start(string $provider, Payment $payment, array $context = []): array
    {
        return $this->measure('start', $provider, function () use ($provider, $payment, $context) {
            return $this->router->for($provider)->start($payment, $context);
        });
    }

    /**
     * Executes finalize operation.
     */
    public function finalize(string $provider, Ulid $id, array $payload = []): Payment
    {
        return $this->measure('finalize', $provider, function () use ($provider, $id, $payload) {
            return $this->router->for($provider)->finalize($id, $payload);
        });
    }

    /**
     * Executes refund operation.
     */
    public function refund(string $provider, Ulid $id, string $amount): Payment
    {
        return $this->measure('refund', $provider, function () use ($provider, $id, $amount) {
            return $this->router->for($provider)->refund($id, $amount);
        });
    }

    /**
     * Executes reconciliation operation.
     */
    public function reconcile(string $provider, Ulid $id): Payment
    {
        return $this->measure('reconcile', $provider, function () use ($provider, $id) {
            return $this->router->for($provider)->reconcile($id);
        });
    }

    /**
     * Wraps provider execution with retry, circuit breaker and metrics.
     *
     * @return mixed Provider result.
     */
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
