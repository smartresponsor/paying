<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

interface PaymentProjectionRepositoryInterface
{
    public function findById(string $id): ?array;

    public function listByStatus(string $status, int $limit = 100): array;

    public function upsert(array $row): void;

    public function maxUpdatedAt(): ?string;

    public function watermark(): ?string;

    public function saveWatermark(string $ts): void;
}
