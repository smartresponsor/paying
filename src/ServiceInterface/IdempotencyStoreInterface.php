<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the idempotency store interface payment service boundary.
 */
interface IdempotencyStoreInterface
{
    /**
     * Returns the value exposed by the get accessor.
     */
    public function get(string $key): ?string;

    /**
     * Provides the put behavior for the idempotency store interface component.
     */
    public function put(string $key, string $value, int $ttlSec): void;

    /**
     * Provides the purge expired behavior for the idempotency store interface component.
     */
    public function purgeExpired(): int;
}
