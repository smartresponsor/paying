<?php

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\MetricInterface;

class Metric implements MetricInterface
{
    private int $success = 0;
    private int $failure = 0;
    private float $sumMs = 0.0;
    private int $countMs = 0;

    private int $retryAttempts = 0;
    private int $retryExhausted = 0;

    private array $providerSuccess = [];
    private array $providerFailure = [];
    private array $providerDuration = [];

    public function incSuccess(): void { ++$this->success; }
    public function incFailure(): void { ++$this->failure; }
    public function observeDuration(float $ms): void { $this->sumMs += $ms; ++$this->countMs; }

    public function incProviderSuccess(string $provider, string $operation): void
    {
        $this->providerSuccess[$provider][$operation] = ($this->providerSuccess[$provider][$operation] ?? 0) + 1;
    }

    public function incProviderFailure(string $provider, string $operation): void
    {
        $this->providerFailure[$provider][$operation] = ($this->providerFailure[$provider][$operation] ?? 0) + 1;
    }

    public function observeProviderDuration(string $provider, string $operation, float $ms): void
    {
        $this->providerDuration[$provider][$operation]['sum'] = ($this->providerDuration[$provider][$operation]['sum'] ?? 0) + $ms;
        $this->providerDuration[$provider][$operation]['count'] = ($this->providerDuration[$provider][$operation]['count'] ?? 0) + 1;
    }

    public function incRetryAttempt(): void { ++$this->retryAttempts; }
    public function incRetryExhausted(): void { ++$this->retryExhausted; }

    public function export(): string
    {
        $avg = $this->countMs ? ($this->sumMs / $this->countMs) : 0.0;

        $lines = [
            "payment_success_total {$this->success}",
            "payment_failure_total {$this->failure}",
            "payment_duration_ms_avg {$avg}",
            "payment_retry_attempts_total {$this->retryAttempts}",
            "payment_retry_exhausted_total {$this->retryExhausted}",
        ];

        foreach ($this->providerSuccess as $provider => $ops) {
            foreach ($ops as $op => $count) {
                $lines[] = sprintf('payment_provider_success_total{provider="%s",operation="%s"} %d', $provider, $op, $count);
            }
        }

        foreach ($this->providerFailure as $provider => $ops) {
            foreach ($ops as $op => $count) {
                $lines[] = sprintf('payment_provider_failure_total{provider="%s",operation="%s"} %d', $provider, $op, $count);
            }
        }

        foreach ($this->providerDuration as $provider => $ops) {
            foreach ($ops as $op => $data) {
                $avgMs = $data['count'] ? ($data['sum'] / $data['count']) : 0;
                $lines[] = sprintf('payment_provider_duration_ms_avg{provider="%s",operation="%s"} %f', $provider, $op, $avgMs);
            }
        }

        return implode("\n", $lines)."\n";
    }
}
