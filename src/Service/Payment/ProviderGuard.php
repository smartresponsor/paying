<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

class ProviderGuard implements ProviderGuardInterface
{
    public function __construct(
        private readonly ProviderRouter $router,
        private readonly RetryExecutorInterface $retry,
        private readonly CircuitBreakerInterface $breaker,
    ) {
    }

    public function start(string $provider, Payment $payment, array $context = []): array
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $res = $this->retry->execute(fn () => $this->router->for($provider)->start($payment, $context));
            $this->breaker->recordSuccess($key);

            return $res;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }

    public function finalize(string $provider, Ulid $id, array $payload = []): Payment
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $p = $this->retry->execute(fn () => $this->router->for($provider)->finalize($id, $payload));
            $this->breaker->recordSuccess($key);

            return $p;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }

    public function refund(string $provider, Ulid $id, string $amount): Payment
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $p = $this->retry->execute(fn () => $this->router->for($provider)->refund($id, $amount));
            $this->breaker->recordSuccess($key);

            return $p;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }

    public function reconcile(string $provider, Ulid $id): Payment
    {
        $key = 'provider:'.$provider;
        if ($this->breaker->isOpen($key)) {
            throw new \RuntimeException('Circuit open for '.$provider);
        }
        try {
            $p = $this->retry->execute(fn () => $this->router->for($provider)->reconcile($id));
            $this->breaker->recordSuccess($key);

            return $p;
        } catch (\Throwable $e) {
            $this->breaker->recordFailure($key);
            throw $e;
        }
    }
}
