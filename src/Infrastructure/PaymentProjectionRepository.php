<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;

/**
 * Persists and queries payment projection records for read-side use cases.
 */
readonly class PaymentProjectionRepository implements PaymentProjectionRepositoryInterface
{
    public function __construct(
        private Connection $infra,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Loads a payment projection by its identifier.
     *
     * @param string $id
     *
     * @return array<string, scalar|null>|null
     *
     * @throws Exception
     */
    public function findById(string $id): ?array
    {
        try {
            $row = $this->infra->fetchAssociative(
                'SELECT id, order_id, amount, currency, status, provider_ref, updated_at FROM payment_projection WHERE id = :id',
                ['id' => $id],
            );
        } catch (Exception $exception) {
            $this->logger->error('Failed to fetch payment projection by ID.', ['id' => $id, 'exception' => $exception]);

            throw $exception;
        }

        return false !== $row ? $row : null;
    }

    /**
     * Lists payment projections filtered by their current status.
     *
     * @param string $status
     * @param int    $limit
     *
     * @return list<array<string, scalar|null>>
     *
     * @throws Exception
     */
    public function listByStatus(string $status, int $limit = 100): array
    {
        try {
            return $this->infra->fetchAllAssociative(
                'SELECT id, order_id, amount, currency, status, provider_ref, updated_at FROM payment_projection WHERE status = :st ORDER BY updated_at DESC LIMIT :lim',
                ['st' => $status, 'lim' => $limit],
                ['st' => ParameterType::STRING, 'lim' => ParameterType::INTEGER],
            );
        } catch (Exception $exception) {
            $this->logger->error('Failed to list payment projections by status.', ['status' => $status, 'limit' => $limit, 'exception' => $exception]);

            throw $exception;
        }
    }

    /**
     * Creates or updates a payment projection snapshot.
     */
    public function upsert(array $row): void
    {
        try {
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
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to upsert payment projection row.', ['row' => $row, 'exception' => $throwable]);

            throw $throwable;
        }
    }

    /**
     * Returns the latest projection update timestamp currently stored.
     */
    public function maxUpdatedAt(): ?string
    {
        try {
            $row = $this->infra->fetchOne('SELECT MAX(updated_at) FROM payment_projection');
        } catch (Exception $exception) {
            $this->logger->error('Failed to read payment projection max updated_at.', ['exception' => $exception]);

            throw $exception;
        }

        return $row ? (string) $row : null;
    }

    /**
     * Returns the stored projection watermark value.
     */
    public function watermark(): ?string
    {
        try {
            $row = $this->infra->fetchOne("SELECT value FROM payment_projection_meta WHERE name = 'watermark'");
        } catch (Exception $exception) {
            $this->logger->error('Failed to read payment projection watermark.', ['exception' => $exception]);

            throw $exception;
        }

        return $row ? (string) $row : null;
    }

    /**
     * Stores the latest processed projection watermark value.
     */
    public function saveWatermark(string $ts): void
    {
        try {
            $updated = $this->infra->update('payment_projection_meta', ['value' => $ts], ['name' => 'watermark']);

            if (0 === $updated) {
                $this->infra->insert('payment_projection_meta', ['name' => 'watermark', 'value' => $ts]);
            }
        } catch (Exception $exception) {
            $this->logger->error('Failed to save payment projection watermark.', ['watermark' => $ts, 'exception' => $exception]);

            throw $exception;
        }
    }
}
