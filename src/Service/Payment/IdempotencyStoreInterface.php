<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface IdempotencyStoreInterface
{
    public function get(string $key): ?string;

    public function put(string $key, string $value, int $ttlSec): void;

    public function purgeExpired(): int;
}
