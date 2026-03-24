<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure\Payment;

interface IdempotencyStoreInterface
{
    public function get(string $key): ?array;

    public function save(string $key, string $payloadHash, array $response, int $statusCode, int $ttlSeconds): void;
}
