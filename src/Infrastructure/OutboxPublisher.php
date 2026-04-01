<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\InfrastructureInterface\OutboxPublisherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Ulid;

readonly class OutboxPublisher implements OutboxPublisherInterface
{
    public function __construct(private Connection $data)
    {
    }

    public function enqueue(string $topic, array $payload): void
    {
        try {
            $this->data->insert('payment_outbox_message', [
                'id' => (new Ulid())->toRfc4122(),
                'type' => $topic,
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'occurred_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'status' => 'pending',
                'attempts' => 0,
                'last_error' => null,
                'routing_key' => $topic,
            ]);
        } catch (Exception $e) {
        } catch (\JsonException $e) {
        }
    }

    public function moveToDlq(string $id, string $reason): void
    {
        $row = false;

        try {
            $row = $this->data->fetchAssociative(
                'SELECT * FROM payment_outbox_message WHERE id = :id',
                ['id' => $id],
            );
        } catch (Exception $e) {
        }

        if (false === $row) {
            return;
        }

        try {
            $this->data->insert('payment_dlq', [
                'outbox_id' => (string) $row['id'],
                'topic' => (string) ($row['routing_key'] ?? $row['type']),
                'payload' => (string) $row['payload'],
                'reason' => $reason,
                'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
        }

        try {
            $this->data->executeStatement(
                'DELETE FROM payment_outbox_message WHERE id = :id',
                ['id' => $id],
            );
        } catch (Exception $e) {
        }
    }
}
