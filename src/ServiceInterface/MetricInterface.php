<?php

declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the metric interface payment service boundary.
 */
interface MetricInterface
{
    /**
     * Provides the inc success behavior for the metric interface component.
     */
    public function incSuccess(): void;

    /**
     * Provides the inc failure behavior for the metric interface component.
     */
    public function incFailure(): void;

    /**
     * Provides the observe duration behavior for the metric interface component.
     */
    public function observeDuration(float $ms): void;

    /**
     * Provides the inc provider success behavior for the metric interface component.
     */
    public function incProviderSuccess(string $provider, string $operation): void;

    /**
     * Provides the inc provider failure behavior for the metric interface component.
     */
    public function incProviderFailure(string $provider, string $operation): void;

    /**
     * Provides the observe provider duration behavior for the metric interface component.
     */
    public function observeProviderDuration(string $provider, string $operation, float $ms): void;

    /**
     * Provides the inc retry attempt behavior for the metric interface component.
     */
    public function incRetryAttempt(): void;

    /**
     * Provides the inc retry exhausted behavior for the metric interface component.
     */
    public function incRetryExhausted(): void;

    /**
     * Provides the export behavior for the metric interface component.
     */
    public function export(): string;
}
