<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\ServiceInterface\IdempotencyStoreInterface;
use Doctrine\DBAL\Connection;

class DbalIdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(private readonly Connection $data)
    {
    }

    public function get(string $key): ?string
    {
        $row = $this->data->fetchAssociative('SELECT value, expires_at FROM payment_idempotency WHERE key = :k', ['k' => $key]);
        if (!$row) {
            return null;
        }
        if (strtotime((string) $row['expires_at']) < time()) {
            $this->data->executeStatement('DELETE FROM payment_idempotency WHERE key = :k', ['k' => $key]);

            return null;
        }

        return (string) $row['value'];
    }

    public function put(string $key, string $value, int $ttlSec): void
    {
        $exp = (new \DateTimeImmutable("+{$ttlSec} seconds"))->format('Y-m-d H:i:s');
        $this->data->executeStatement(
            'INSERT INTO payment_idempotency(key, value, expires_at) VALUES(:k,:v,:e)
             ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, expires_at = EXCLUDED.expires_at',
            ['k' => $key, 'v' => $value, 'e' => $exp]
        );
    }

    public function purgeExpired(): int
    {
        return (int) $this->data->executeStatement('DELETE FROM payment_idempotency WHERE expires_at < NOW()');
    }
}
