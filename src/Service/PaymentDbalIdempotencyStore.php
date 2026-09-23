<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\RepositoryInterface\PaymentOperationalRepositoryInterface;
use App\Paying\ServiceInterface\PaymentIdempotencyStoreInterface;

/**
 * Stores payment idempotency keys in the relational operational database.
 */
readonly class PaymentDbalIdempotencyStore implements PaymentIdempotencyStoreInterface
{
    public function __construct(private PaymentOperationalRepositoryInterface $operations)
    {
    }

    /**
     * Loads a stored idempotency value when the key is present and not expired.
     */
    public function get(string $key): ?string
    {
        return $this->operations->idempotencyGet($key);
    }

    /**
     * Stores or refreshes an idempotency value with a new expiration window.
     */
    public function put(string $key, string $value, int $ttlSec): void
    {
        $this->operations->idempotencyPut($key, $value, $ttlSec);
    }

    /**
     * Removes expired idempotency records and returns the affected row count.
     */
    public function purgeExpired(): int
    {
        return $this->operations->purgeExpiredIdempotency();
    }
}
