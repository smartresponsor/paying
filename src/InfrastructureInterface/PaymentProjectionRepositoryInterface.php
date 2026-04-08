<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

/**
 * Defines the read-side repository contract for payment projections.
 */
interface PaymentProjectionRepositoryInterface
{
    /**
     * Loads a payment projection by its identifier.
     *
     * @return array<string, scalar|null>|null
     */
    public function findById(string $id): ?array;

    /**
     * Lists payment projections filtered by their current status.
     *
     * @return list<array<string, scalar|null>>
     */
    public function listByStatus(string $status, int $limit = 100): array;

    /**
     * Creates or updates a payment projection snapshot.
     */
    public function upsert(array $row): void;

    /**
     * Returns the latest projection update timestamp currently stored.
     */
    public function maxUpdatedAt(): ?string;

    /**
     * Returns the stored projection watermark value.
     */
    public function watermark(): ?string;

    /**
     * Stores the latest processed projection watermark value.
     */
    public function saveWatermark(string $ts): void;
}
