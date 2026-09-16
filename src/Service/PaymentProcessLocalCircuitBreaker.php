<?php

declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\ServiceInterface\PaymentCircuitBreakerInterface;

final class PaymentProcessLocalCircuitBreaker implements PaymentCircuitBreakerInterface
{
    /** @var array<string, array{failures: int, retryAt: int}> */
    private array $state = [];

    public function __construct(
        private readonly int $threshold = 5,
        private readonly int $cooldownSec = 60,
    ) {
    }

    public function isOpen(string $key): bool
    {
        $state = $this->state[$key] ?? null;

        return is_array($state) && $state['failures'] >= $this->threshold && time() < $state['retryAt'];
    }

    public function recordSuccess(string $key): void
    {
        unset($this->state[$key]);
    }

    public function recordFailure(string $key): void
    {
        $this->state[$key] = [
            'failures' => ($this->state[$key]['failures'] ?? 0) + 1,
            'retryAt' => time() + $this->cooldownSec,
        ];
    }
}
