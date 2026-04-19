<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\DlqServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the dlq service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class DlqService implements DlqServiceInterface
{
    public function __construct(private Connection $data)
    {
    }

    /**
     * Returns the collection assembled by the list query path.
     *
     * @return list<array{id: int, outbox_id: string, topic: string, reason: string, created_at: string}>
     *
     * @throws Exception
     */
    public function list(): array
    {
        $rows = $this->data->fetchAllAssociative(
            'SELECT id, outbox_id, topic, reason, created_at FROM payment_dlq ORDER BY id DESC LIMIT 200',
        );

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'outbox_id' => (string) $row['outbox_id'],
                'topic' => (string) $row['topic'],
                'reason' => (string) $row['reason'],
                'created_at' => (string) $row['created_at'],
            ],
            $rows,
        );
    }

    /**
     * Executes the replay operation for the current payment workflow.
     *
     * @throws Exception
     */
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
