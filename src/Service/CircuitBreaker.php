<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;
use App\ServiceInterface\CircuitBreakerInterface;

use Doctrine\DBAL\Connection;

class CircuitBreaker implements CircuitBreakerInterface
{
    public function __construct(private readonly Connection $data, private readonly int $threshold = 5, private readonly int $cooldownSec = 60)
    {
    }

    public function isOpen(string $key): bool
    {
        $row = $this->data->fetchAssociative('SELECT failure_count, retry_at FROM payment_circuit WHERE key = :k', ['k' => $key]);
        if (!$row) {
            return false;
        }
        $fail = (int) $row['failure_count'];
        $retryAt = strtotime((string) $row['retry_at'] ?: '1970-01-01 00:00:00');
        if ($fail >= $this->threshold && time() < $retryAt) {
            return true;
        }

        return false;
    }

    public function recordSuccess(string $key): void
    {
        $this->data->executeStatement('DELETE FROM payment_circuit WHERE key = :k', ['k' => $key]);
    }

    public function recordFailure(string $key): void
    {
        $row = $this->data->fetchAssociative('SELECT failure_count FROM payment_circuit WHERE key = :k', ['k' => $key]);
        $count = $row ? (int) $row['failure_count'] + 1 : 1;
        $retryAt = (new \DateTimeImmutable())->modify('+'.$this->cooldownSec.' seconds')->format('Y-m-d H:i:s');
        $this->data->executeStatement(
            'INSERT INTO payment_circuit(key, failure_count, retry_at) VALUES (:k,:c,:r) ON CONFLICT (key) DO UPDATE SET failure_count = EXCLUDED.failure_count, retry_at = EXCLUDED.retry_at',
            ['k' => $key, 'c' => $count, 'r' => $retryAt]
        );
    }
}
