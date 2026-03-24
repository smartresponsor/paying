<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;

interface CircuitBreakerInterface
{
    public function isOpen(string $key): bool;

    public function recordSuccess(string $key): void;

    public function recordFailure(string $key): void;
}
