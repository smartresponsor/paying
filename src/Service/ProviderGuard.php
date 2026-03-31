<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\CircuitBreakerInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RetryExecutorInterface;
use Symfony\Component\Uid\Ulid;

readonly class ProviderGuard implements ProviderGuardInterface
{
    public function __construct(
        private ProviderRouter $router,
        private RetryExecutorInterface $retry,
        private CircuitBreakerInterface $breaker,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Throwable
     */
    public function start(string $provider, Payment $payment, array $context = []): array
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $result = $this->retry->execute(fn () => $this->router->for($provider)->start($payment, $context));
            $this->breaker->recordSuccess($key);

            return $result;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }

    /**
     * @throws \Throwable
     */
    public function finalize(string $provider, Ulid $id, array $payload = []): Payment
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $payment = $this->retry->execute(fn () => $this->router->for($provider)->finalize($id, $payload));
            $this->breaker->recordSuccess($key);

            return $payment;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }

    /**
     * @throws \Throwable
     */
    public function refund(string $provider, Ulid $id, string $amount): Payment
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $payment = $this->retry->execute(fn () => $this->router->for($provider)->refund($id, $amount));
            $this->breaker->recordSuccess($key);

            return $payment;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }

    /**
     * @throws \Throwable
     */
    public function reconcile(string $provider, Ulid $id): Payment
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $payment = $this->retry->execute(fn () => $this->router->for($provider)->reconcile($id));
            $this->breaker->recordSuccess($key);

            return $payment;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }
}
