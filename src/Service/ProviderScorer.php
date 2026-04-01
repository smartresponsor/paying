<?php

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\CircuitBreakerInterface;
use App\ServiceInterface\MetricInterface;
use App\ServiceInterface\ProviderScorerInterface;

final readonly class ProviderScorer implements ProviderScorerInterface
{
    public function __construct(
        private MetricInterface $metric,
        private CircuitBreakerInterface $breaker,
    ) {}

    public function rank(array $candidates, string $operation): array
    {
        $export = $this->metric->export();

        $result = [];

        foreach ($candidates as $provider) {
            $success = $this->extract($export, 'payment_provider_success_total', $provider, $operation);
            $failure = $this->extract($export, 'payment_provider_failure_total', $provider, $operation);
            $duration = $this->extractFloat($export, 'payment_provider_duration_ms_avg', $provider, $operation);

            $total = $success + $failure;
            $successRate = $total > 0 ? ($success / $total) : 1.0;

            $available = !$this->breaker->isOpen('provider:'.$provider);

            $score = $successRate;
            $score -= min(1.0, $duration / 1000.0); // penalty for latency
            if (!$available) {
                $score -= 1.0;
            }

            $result[] = [
                'provider' => $provider,
                'score' => $score,
                'available' => $available,
                'successRate' => $successRate,
                'avgDurationMs' => $duration,
            ];
        }

        usort($result, static fn ($a, $b) => $b['score'] <=> $a['score']);

        return $result;
    }

    public function choose(array $candidates, string $operation): string
    {
        $ranked = $this->rank($candidates, $operation);

        return $ranked[0]['provider'] ?? throw new \RuntimeException('No providers');
    }

    private function extract(string $export, string $metric, string $provider, string $operation): int
    {
        $pattern = sprintf('/%s\{provider="%s",operation="%s"\} (\d+)/', $metric, $provider, $operation);

        if (preg_match($pattern, $export, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function extractFloat(string $export, string $metric, string $provider, string $operation): float
    {
        $pattern = sprintf('/%s\{provider="%s",operation="%s"\} ([0-9\.]+)/', $metric, $provider, $operation);

        if (preg_match($pattern, $export, $m)) {
            return (float) $m[1];
        }

        return 0.0;
    }
}
