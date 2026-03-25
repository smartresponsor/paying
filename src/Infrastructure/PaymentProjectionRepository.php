<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure;

use App\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class PaymentProjectionRepository implements PaymentProjectionRepositoryInterface
{
    public function __construct(private readonly Connection $infra)
    {
    }

    /** @return array<string, scalar|null>|null */
    public function findById(string $id): ?array
    {
        $row = $this->infra->fetchAssociative(
            'SELECT id, amount, currency, status, updated_at FROM payment_projection WHERE id = :id',
            ['id' => $id],
        );

        return $row ?: null;
    }

    /** @return list<array<string, scalar|null>> */
    public function listByStatus(string $status, int $limit = 100): array
    {
        return $this->infra->fetchAllAssociative(
            'SELECT id, amount, currency, status, updated_at FROM payment_projection WHERE status = :st ORDER BY updated_at DESC LIMIT :lim',
            ['st' => $status, 'lim' => $limit],
            ['st' => ParameterType::STRING, 'lim' => ParameterType::INTEGER],
        );
    }

    /** @param array<string, scalar|null> $row */
    public function upsert(array $row): void
    {
        $this->infra->transactional(function (Connection $connection) use ($row): void {
            $id = (string) ($row['id'] ?? '');
            if ('' === $id) {
                throw new \InvalidArgumentException('Projection row id is required.');
            }

            $payload = [
                'amount' => (string) ($row['amount'] ?? '0.00'),
                'currency' => (string) ($row['currency'] ?? ''),
                'status' => (string) ($row['status'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ];

            $updated = $connection->update('payment_projection', $payload, ['id' => $id]);
            if (0 === $updated) {
                $connection->insert('payment_projection', ['id' => $id] + $payload);
            }
        });
    }

    public function maxUpdatedAt(): ?string
    {
        $row = $this->infra->fetchOne('SELECT MAX(updated_at) FROM payment_projection');

        return $row ? (string) $row : null;
    }

    public function watermark(): ?string
    {
        $row = $this->infra->fetchOne("SELECT value FROM payment_projection_meta WHERE name = 'watermark'");

        return $row ? (string) $row : null;
    }

    public function saveWatermark(string $ts): void
    {
        $updated = $this->infra->update('payment_projection_meta', ['value' => $ts], ['name' => 'watermark']);
        if (0 === $updated) {
            $this->infra->insert('payment_projection_meta', ['name' => 'watermark', 'value' => $ts]);
        }
    }
}
