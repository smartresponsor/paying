<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Uid\Ulid;

final class DlqService implements DlqServiceInterface
{
    public function __construct(private readonly Connection $data)
    {
    }

    public function list(): array
    {
        return $this->data->fetchAllAssociative(
            'SELECT id, outbox_id, topic, reason, created_at FROM payment_dlq ORDER BY id DESC LIMIT 200',
        );
    }

    public function replay(int $id): bool
    {
        $row = $this->data->fetchAssociative(
            'SELECT * FROM payment_dlq WHERE id = :id',
            ['id' => $id],
            ['id' => ParameterType::INTEGER],
        );

        if (false === $row) {
            return false;
        }

        $this->data->insert('payment_outbox_message', [
            'id' => (new Ulid())->toRfc4122(),
            'type' => (string) $row['topic'],
            'payload' => (string) $row['payload'],
            'occurred_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => null,
            'routing_key' => (string) $row['topic'],
        ]);
        $this->data->executeStatement(
            'DELETE FROM payment_dlq WHERE id = :id',
            ['id' => $id],
            ['id' => ParameterType::INTEGER],
        );

        return true;
    }
}
