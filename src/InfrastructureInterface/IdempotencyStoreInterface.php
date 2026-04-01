<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

interface IdempotencyStoreInterface
{
    /** @return array{response: array<string, mixed>|list<mixed>|scalar|null, hash: string}|null */
    public function get(string $key): ?array;

    public function save(string $key, string $payloadHash, array $response, int $statusCode, int $ttlSeconds): void;
}
