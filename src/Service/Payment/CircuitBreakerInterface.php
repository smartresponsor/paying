<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface CircuitBreakerInterface
{
    public function isOpen(string $key): bool;

    public function recordSuccess(string $key): void;

    public function recordFailure(string $key): void;
}
