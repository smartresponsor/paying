<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\CircuitBreakerInterface;
use App\ServiceInterface\MetricInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RetryExecutorInterface;
use Symfony\Component\Uid\Ulid;

readonly class ProviderGuard implements ProviderGuardInterface
{
    public function __construct(
        private ProviderRouter $router,
        private RetryExecutorInterface $retry,
        private CircuitBreakerInterface $breaker,
        private MetricInterface $metric,
    ) {}

    public function start(string $provider, Payment $payment, array $context = []): array
    {
        return $this->measure('start', $provider, function () use ($provider, $payment, $context) {
            return $this->router->for($provider)->start($payment, $context);
        });
    }

    public function finalize(string $provider, Ulid $id, array $payload = []): Payment
    {
        return $this->measure('finalize', $provider, function () use ($provider, $id, $payload) {
            return $this->router->for($provider)->finalize($id, $payload);
        });
    }

    public function refund(string $provider, Ulid $id, string $amount): Payment
    {
        return $this->measure('refund', $provider, function () use ($provider, $id, $amount) {
            return $this->router->for($provider)->refund($id, $amount);
        });
    }

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
