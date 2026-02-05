<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface CircuitBreakerInterface
{
    public function isOpen(string $key): bool;
    public function recordSuccess(string $key): void;
    public function recordFailure(string $key): void;
}
