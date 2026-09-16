<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\RepositoryInterface;

use App\Paying\Entity\PaymentEntity;

/**
 * Defines the persistence contract for the payment repository interface payment read-write operations.
 */
interface PaymentRepositoryInterface
{
    /**
     * Executes the save operation for the current payment workflow.
     */
    public function save(PaymentEntity $payment): void;

    /**
     * Looks up payment data through the find query path.
     */
    public function find(string $id): ?PaymentEntity;

    /**
     * Looks up payment data through the find by order id query path.
     */
    public function findByOrderId(string $orderId): ?PaymentEntity;

    /**
     * Returns the collection assembled by the list recent query path.
     *
     * @return list<PaymentEntity>
     */
    public function listRecent(int $limit = 10): array;

    /**
     * Returns the collection assembled by the list ids by statuses query path.
     *
     * @return list<string>
     */
    public function listIdsByStatuses(array $statuses, int $limit = 100): array;

    /**
     * Returns the collection assembled from payments updated after the provided timestamp.
     *
     * @return list<PaymentEntity>
     */
    public function listUpdatedAfter(\DateTimeImmutable $updatedAfter, int $limit = 500): array;

    /**
     * Returns the collection assembled by the list all ordered by updated at query path.
     *
     * @return list<PaymentEntity>
     */
    public function listAllOrderedByUpdatedAt(int $limit = 1000, int $offset = 0): array;

    /**
     * Returns the latest updated timestamp stored for any payment, if available.
     */
    public function maxUpdatedAt(): ?string;

    /**
     * Returns status counts for payments updated since the provided timestamp.
     *
     * @return array<string, int>
     */
    public function countByStatusSince(\DateTimeImmutable $since): array;
}
