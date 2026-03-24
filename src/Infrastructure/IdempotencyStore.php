<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure;

use Doctrine\DBAL\Connection;

/**
 * Legacy HTTP-response idempotency store.
 *
 * This class remains in the repository because parts of the infrastructure
 * contour still reference the legacy response-store shape. The active runtime
 * path uses App\ServiceInterface\IdempotencyStoreInterface via
 * DbalIdempotencyStore/RedisIdempotencyStore, but this class must still remain
 * signature-compatible with its own infrastructure interface so Symfony can
 * compile the container under Symfony 8 / PHP 8.4.
 */
class IdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(private readonly Connection $data)
    {
    }

    public function get(string $key): ?array
    {
        $row = $this->data->fetchAssociative(
            'SELECT response, hash FROM payment_idempotency WHERE idempotency_key = :k',
            ['k' => $key]
        );

        if (!$row) {
            return null;
        }

        return [
            'response' => json_decode((string) $row['response'], true),
            'hash' => (string) $row['hash'],
        ];
    }

    public function save(string $key, string $payloadHash, array $response, int $statusCode, int $ttlSeconds): void
    {
        $json = json_encode($response, JSON_THROW_ON_ERROR);

        $this->data->executeStatement(
            'INSERT INTO payment_idempotency (idempotency_key, hash, response, created_at) '
            .'VALUES (:k, :h, :r, NOW()) '
            .'ON CONFLICT (idempotency_key) DO UPDATE SET '
            .'hash = EXCLUDED.hash, response = EXCLUDED.response, created_at = EXCLUDED.created_at',
            [
                'k' => $key,
                'h' => $payloadHash,
                'r' => $json,
            ]
        );
    }
}
