<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Infrastructure\Payment;

interface IdempotencyStoreInterface
{
    public function get(string $key): ?array;

    public function save(string $key, string $payloadHash, array $response, int $statusCode, int $ttlSeconds): void;
}
