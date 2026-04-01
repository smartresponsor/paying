<?php

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\CircuitBreakerInterface;
use App\ServiceInterface\ProviderSelectorInterface;

final readonly class ProviderSelector implements ProviderSelectorInterface
{
    public function __construct(
        private CircuitBreakerInterface $breaker,
    ) {}

    public function choose(array $candidates): string
    {
        foreach ($candidates as $provider) {
            if (!$this->breaker->isOpen('provider:'.$provider)) {
                return $provider;
            }
        }

        // fallback: return first even if open
        return $candidates[0] ?? throw new \RuntimeException('No providers given');
    }

    public function explain(array $candidates): array
    {
        $result = [];

        foreach ($candidates as $provider) {
            $open = $this->breaker->isOpen('provider:'.$provider);

            $result[] = [
                'provider' => $provider,
                'available' => !$open,
                'reason' => $open ? 'circuit_open' : 'ok',
            ];
        }

        return $result;
    }
}
