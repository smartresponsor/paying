<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;

readonly class PaymentProjectionRepository implements PaymentProjectionRepositoryInterface
{
    public function __construct(
        private Connection $infra,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<string, scalar|null>|null
     */
    public function findById(string $id): ?array
    {
        try {
            $row = $this->infra->fetchAssociative(
                'SELECT id, order_id, amount, currency, status, provider_ref, updated_at FROM payment_projection WHERE id = :id',
                ['id' => $id],
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to fetch payment projection by ID.', ['id' => $id, 'exception' => $e]);

            throw $e;
        }

        return false !== $row ? $row : null;
    }

    /**
     * @return list<array<string, scalar|null>>
     */
    public function listByStatus(string $status, int $limit = 100): array
    {
        try {
            return $this->infra->fetchAllAssociative(
                'SELECT id, order_id, amount, currency, status, provider_ref, updated_at FROM payment_projection WHERE status = :st ORDER BY updated_at DESC LIMIT :lim',
                ['st' => $status, 'lim' => $limit],
                ['st' => ParameterType::STRING, 'lim' => ParameterType::INTEGER],
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to list payment projections by status.', ['status' => $status, 'limit' => $limit, 'exception' => $e]);

            throw $e;
        }
    }

    public function upsert(array $row): void
    {
        $this->infra->transactional(function (Connection $connection) use ($row): void {
            $id = (string) ($row['id'] ?? '');
            if ('' === $id) {
                throw new \InvalidArgumentException('Projection row id is required.');
            }

            $payload = [
                'order_id' => (string) ($row['order_id'] ?? ($row['orderId'] ?? '')),
                'amount' => (string) ($row['amount'] ?? '0.00'),
                'currency' => (string) ($row['currency'] ?? ''),
                'status' => (string) ($row['status'] ?? ''),
                'provider_ref' => isset($row['provider_ref'])
                    ? (string) $row['provider_ref']
                    : (isset($row['providerRef']) ? (string) $row['providerRef'] : null),
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
        try {
            $row = $this->infra->fetchOne('SELECT MAX(updated_at) FROM payment_projection');
        } catch (Exception $e) {
            $this->logger->error('Failed to read payment projection max updated_at.', ['exception' => $e]);

            throw $e;
        }

        return $row ? (string) $row : null;
    }

    public function watermark(): ?string
    {
        try {
            $row = $this->infra->fetchOne("SELECT value FROM payment_projection_meta WHERE name = 'watermark'");
        } catch (Exception $e) {
            $this->logger->error('Failed to read payment projection watermark.', ['exception' => $e]);

            throw $e;
        }

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
