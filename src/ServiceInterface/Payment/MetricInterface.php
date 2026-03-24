<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Payment;

interface MetricInterface
{
    public function incSuccess(): void;

    public function incFailure(): void;

    public function observeDuration(float $ms): void;

    public function export(): string;
}
