<?php

declare(strict_types=1);

namespace App\ServiceInterface;

interface MetricInterface
{
    public function incSuccess(): void;
    public function incFailure(): void;
    public function observeDuration(float $ms): void;

    public function incProviderSuccess(string $provider, string $operation): void;
    public function incProviderFailure(string $provider, string $operation): void;
    public function observeProviderDuration(string $provider, string $operation, float $ms): void;

    public function export(): string;
}
