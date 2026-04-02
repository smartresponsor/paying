<?php

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\MetricInterface;
use Psr\Cache\CacheItemPoolInterface;

final class Metric implements MetricInterface
{
    private const CACHE_KEY = 'payment.metric.state';

    public function __construct(private CacheItemPoolInterface $cache)
    {
    }

    public function incSuccess(): void
    {
        $state = $this->loadState();
        $state['success']++;
        $this->saveState($state);
    }

    public function incFailure(): void
    {
        $state = $this->loadState();
        $state['failure']++;
        $this->saveState($state);
    }

    public function observeDuration(float $ms): void
    {
        $state = $this->loadState();
        $state['sumMs'] += $ms;
        $state['countMs']++;
        $this->saveState($state);
    }

    public function incProviderSuccess(string $provider, string $operation): void
    {
        $state = $this->loadState();
        $state['providerSuccess'][$provider][$operation] = ($state['providerSuccess'][$provider][$operation] ?? 0) + 1;
        $this->saveState($state);
    }

    public function incProviderFailure(string $provider, string $operation): void
    {
        $state = $this->loadState();
        $state['providerFailure'][$provider][$operation] = ($state['providerFailure'][$provider][$operation] ?? 0) + 1;
        $this->saveState($state);
    }

    public function observeProviderDuration(string $provider, string $operation, float $ms): void
    {
        $state = $this->loadState();
        $state['providerDuration'][$provider][$operation]['sum'] = ($state['providerDuration'][$provider][$operation]['sum'] ?? 0.0) + $ms;
        $state['providerDuration'][$provider][$operation]['count'] = ($state['providerDuration'][$provider][$operation]['count'] ?? 0) + 1;
        $this->saveState($state);
    }

    public function incRetryAttempt(): void
    {
        $state = $this->loadState();
        $state['retryAttempts']++;
        $this->saveState($state);
    }

    public function incRetryExhausted(): void
    {
        $state = $this->loadState();
        $state['retryExhausted']++;
        $this->saveState($state);
    }

    public function export(): string
    {
        $state = $this->loadState();
        $avg = $state['countMs'] > 0 ? ($state['sumMs'] / $state['countMs']) : 0.0;

        $lines = [
            '# TYPE payment_success_total counter',
            sprintf('payment_success_total %d', $state['success']),
            '# TYPE payment_failure_total counter',
            sprintf('payment_failure_total %d', $state['failure']),
            '# TYPE payment_duration_ms_avg gauge',
            sprintf('payment_duration_ms_avg %F', $avg),
            '# TYPE payment_retry_attempts_total counter',
            sprintf('payment_retry_attempts_total %d', $state['retryAttempts']),
            '# TYPE payment_retry_exhausted_total counter',
            sprintf('payment_retry_exhausted_total %d', $state['retryExhausted']),
        ];

        foreach ($state['providerSuccess'] as $provider => $ops) {
            foreach ($ops as $operation => $count) {
                $lines[] = sprintf('payment_provider_success_total{provider="%s",operation="%s"} %d', $provider, $operation, $count);
            }
        }

        foreach ($state['providerFailure'] as $provider => $ops) {
            foreach ($ops as $operation => $count) {
                $lines[] = sprintf('payment_provider_failure_total{provider="%s",operation="%s"} %d', $provider, $operation, $count);
            }
        }

        foreach ($state['providerDuration'] as $provider => $ops) {
            foreach ($ops as $operation => $data) {
                $avgMs = ($data['count'] ?? 0) > 0 ? ($data['sum'] / $data['count']) : 0.0;
                $lines[] = sprintf('payment_provider_duration_ms_avg{provider="%s",operation="%s"} %F', $provider, $operation, $avgMs);
            }
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @return array<string, mixed>
     */
    private function loadState(): array
    {
        $item = $this->cache->getItem(self::CACHE_KEY);
        $state = $item->isHit() ? $item->get() : null;

        if (!is_array($state)) {
            return [
                'success' => 0,
                'failure' => 0,
                'sumMs' => 0.0,
                'countMs' => 0,
                'retryAttempts' => 0,
                'retryExhausted' => 0,
                'providerSuccess' => [],
                'providerFailure' => [],
                'providerDuration' => [],
            ];
        }

        return $state;
    }

    /**
     * @param array<string, mixed> $state
     */
    private function saveState(array $state): void
    {
        $item = $this->cache->getItem(self::CACHE_KEY);
        $item->set($state);
        $this->cache->save($item);
    }
}
