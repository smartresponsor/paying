<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\RepositoryInterface;

use App\Paying\Entity\Payment;

/**
 * Defines the persistence contract for the payment repository interface payment read-write operations.
 */
interface PaymentRepositoryInterface
{
    /**
     * Executes the save operation for the current payment workflow.
     */
    public function save(Payment $payment): void;

    /**
     * Looks up payment data through the find query path.
     */
    public function find(string $id): ?Payment;

    /**
     * Looks up payment data through the find by order id query path.
     */
    public function findByOrderId(string $orderId): ?Payment;

    /**
     * Returns the collection assembled by the list recent query path.
     *
     * @return list<Payment>
     */
    public function listRecent(int $limit = 10): array;

    /**
     * Returns the collection assembled by the list ids by statuses query path.
     *
     * @return list<string>
     */
    public function listIdsByStatuses(array $statuses, int $limit = 100): array;
}
