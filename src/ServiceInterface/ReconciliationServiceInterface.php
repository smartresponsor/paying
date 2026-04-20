<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Entity\Payment;
use Symfony\Component\Uid\Ulid;

/**
 * Defines the contract for the reconciliation service interface payment service boundary.
 */
interface ReconciliationServiceInterface
{
    /**
     * Executes the reconcile operation for the current payment workflow.
     */
    public function reconcile(Ulid $id, string $provider = 'internal'): Payment;

    /**
     * Returns the collection assembled by the list processing ids query path.
     *
     * @return list<string>
     */
    public function listProcessingIds(int $limit = 100): array;
}
