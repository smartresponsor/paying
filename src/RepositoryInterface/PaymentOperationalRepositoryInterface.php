<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\RepositoryInterface;

use App\Paying\Entity\Operational\PaymentCircuitEntity;

/**
 * Defines persistence operations for Paying's operational database.
 */
interface PaymentOperationalRepositoryInterface
{
    public function idempotencyGet(string $key): ?string;

    public function idempotencyPut(string $key, string $value, int $ttlSec): void;

    public function purgeExpiredIdempotency(): int;

    /** @param array<string, mixed> $context */
    public function writeAudit(string $action, array $context = []): void;

    public function findCircuit(string $key): ?PaymentCircuitEntity;

    public function saveCircuit(PaymentCircuitEntity $entity): void;

    public function removeCircuit(PaymentCircuitEntity $entity): void;
}
