<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the circuit breaker interface payment service boundary.
 */
interface CircuitBreakerInterface
{
    /**
     * Determines whether the is open condition is currently satisfied.
     */
    public function isOpen(string $key): bool;

    /**
     * Records the state transition performed by the record success operation.
     */
    public function recordSuccess(string $key): void;

    /**
     * Records the state transition performed by the record failure operation.
     */
    public function recordFailure(string $key): void;
}
