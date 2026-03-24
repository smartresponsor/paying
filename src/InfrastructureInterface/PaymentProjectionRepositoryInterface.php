<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\InfrastructureInterface;

interface PaymentProjectionRepositoryInterface
{
    public function findById(string $id): ?array;

    public function listByStatus(string $status, int $limit = 100): array;

    public function upsert(array $row): void;

    public function maxUpdatedAt(): ?string;

    public function watermark(): ?string;

    public function saveWatermark(string $ts): void;
}
