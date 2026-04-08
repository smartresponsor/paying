<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

/**
 * Defines the legacy response idempotency contract for infrastructure compatibility.
 */
interface IdempotencyStoreInterface
{
    /**
     * Loads a stored idempotent response payload for the supplied key.
     *
     * @return array{response: array<string, mixed>|list<mixed>|scalar|null, hash: string}|null
     */
    public function get(string $key): ?array;

    /**
     * Stores a legacy idempotent response payload for the supplied key.
     */
    public function save(string $key, string $payloadHash, array $response, int $statusCode, int $ttlSeconds): void;
}
