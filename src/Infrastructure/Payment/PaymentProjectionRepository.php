<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use App\InfrastructureInterface\Payment\PaymentProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;

class PaymentProjectionRepository implements PaymentProjectionRepositoryInterface
{
    public function __construct(private readonly Connection $infra) {}

    public function findById(string $id): ?array
    {
        $row = $this->infra->fetchAssociative('SELECT id, amount, currency, status, updated_at FROM payment_projection WHERE id = :id', ['id'=>$id]);
        return $row ?: null;
    }

    public function listByStatus(string $status, int $limit = 100): array
    {
        return $this->infra->fetchAllAssociative('SELECT id, amount, currency, status, updated_at FROM payment_projection WHERE status = :st ORDER BY updated_at DESC LIMIT :lim', ['st'=>$status, 'lim'=>$limit]);
    }

    public function upsert(array $row): void
    {
        $sql = 'INSERT INTO payment_projection (id, amount, currency, status, updated_at) VALUES (:id,:amount,:currency,:status,:updated_at)
                ON DUPLICATE KEY UPDATE amount=VALUES(amount), currency=VALUES(currency), status=VALUES(status), updated_at=VALUES(updated_at)';
        $this->infra->executeStatement($sql, [
            'id'=>$row['id'],
            'amount'=>$row['amount'],
            'currency'=>$row['currency'],
            'status'=>$row['status'],
            'updated_at'=>$row['updated_at'],
        ]);
    }

    public function maxUpdatedAt(): ?string
    {
        $row = $this->infra->fetchOne('SELECT MAX(updated_at) FROM payment_projection');
        return $row ? (string)$row : null;
    }
}
