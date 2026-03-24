<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Payment;

interface IdempotencyStoreInterface
{
    public function get(string $key): ?string;

    public function put(string $key, string $value, int $ttlSec): void;

    public function purgeExpired(): int;
}
