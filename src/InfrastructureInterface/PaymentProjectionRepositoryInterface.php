<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

interface PaymentProjectionRepositoryInterface
{
    /** @return array<string, scalar|null>|null */
    public function findById(string $id): ?array;

    /** @return list<array<string, scalar|null>> */
    public function listByStatus(string $status, int $limit = 100): array;

    /** @param array<string, scalar|null> $row */
    public function upsert(array $row): void;

    public function maxUpdatedAt(): ?string;

    public function watermark(): ?string;

    public function saveWatermark(string $ts): void;
}
