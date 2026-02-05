<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\InfrastructureInterface\Payment;

interface PaymentProjectionRepositoryInterface
{
    /** @return array{id:string, amount:string, currency:string, status:string, updated_at:string}|null */
    public function findById(string $id): ?array;

    /** @return list<array{id:string, amount:string, currency:string, status:string, updated_at:string}> */
    public function listByStatus(string $status, int $limit = 100): array;

    public function upsert(array $row): void;

    public function maxUpdatedAt(): ?string;
}
