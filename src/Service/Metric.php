<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\MetricInterface;

class Metric implements MetricInterface
{
    private int $success = 0;
    private int $failure = 0;
    private float $sumMs = 0.0;
    private int $countMs = 0;

    public function incSuccess(): void
    {
        ++$this->success;
    }

    public function incFailure(): void
    {
        ++$this->failure;
    }

    public function observeDuration(float $ms): void
    {
        $this->sumMs += $ms;
        ++$this->countMs;
    }

    public function export(): string
    {
        $avg = $this->countMs ? ($this->sumMs / $this->countMs) : 0.0;

        return "payment_success_total {$this->success}\n"
            ."payment_failure_total {$this->failure}\n"
            ."payment_duration_ms_avg {$avg}\n";
    }
}
