<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the dlq service interface payment service boundary.
 */
interface PaymentDlqServiceInterface
{
    /**
     * Returns the collection assembled by the list query path.
     *
     * @return list<array{id: int, outbox_id: string, topic: string, reason: string, created_at: string}>
     */
    public function list(): array;

    /**
     * Executes the replay operation for the current payment workflow.
     */
    public function replay(int $id): bool;
}
