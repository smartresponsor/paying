<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use App\InfrastructureInterface\Payment\IdempotencyStoreInterface;
use Doctrine\DBAL\Connection;

class IdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(private readonly Connection $data) {}

    public function get(string $key): ?array
    {
        $row = $this->data->fetchAssociative('SELECT response, hash FROM payment_idempotency WHERE idempotency_key = :k', ['k'=>$key]);
        if (!$row) return null;
        return ['response' => json_decode($row['response'], true), 'hash' => (string)$row['hash']];
    }

    public function save(string $key, string $hash, array $response): void
    {
        $json = json_encode($response);
        $this->data->executeStatement(
            'INSERT INTO payment_idempotency (idempotency_key, hash, response, created_at) VALUES (:k,:h,:r,NOW()) ON CONFLICT (idempotency_key) DO UPDATE SET hash = EXCLUDED.hash, response = EXCLUDED.response, created_at = EXCLUDED.created_at',
            ['k'=>$key,'h'=>$hash,'r'=>$json]
        );
    }
}
