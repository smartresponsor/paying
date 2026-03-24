<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface MetricInterface
{
    public function incSuccess(): void;

    public function incFailure(): void;

    public function observeDuration(float $ms): void;

    public function export(): string;
}
