<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface IdempotencyStoreInterface
{
    public function get(string $key): ?string;
    public function put(string $key, string $value, int $ttlSec): void;
    public function purgeExpired(): int;
}
