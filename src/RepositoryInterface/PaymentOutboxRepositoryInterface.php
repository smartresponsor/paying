<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\RepositoryInterface;

use App\Paying\Entity\Business\PaymentDlqEntity;
use App\Paying\Entity\Business\PaymentOutboxMessageEntity;

/**
 * Defines persistence operations for the payment outbox and dead-letter queue.
 */
interface PaymentOutboxRepositoryInterface
{
    /** @return list<PaymentOutboxMessageEntity> */
    public function listProcessable(int $limit, bool $retryFailed): array;

    public function flush(): void;

    /** @param array<string, mixed> $payload */
    public function enqueue(string $topic, array $payload): void;

    public function moveToDlq(string $id, string $reason): bool;

    /** @return list<PaymentDlqEntity> */
    public function listDlq(int $limit = 200): array;

    public function replayDlq(int $id): bool;
}
