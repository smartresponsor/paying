<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\Exception\OutboxOperationException;
use App\InfrastructureInterface\OutboxPublisherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

readonly class OutboxPublisher implements OutboxPublisherInterface
{
    public function __construct(
        private Connection $data,
        private LoggerInterface $logger,
    ) {
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
        } catch (Exception|\JsonException $e) {
            $this->logger->error('Failed to enqueue payment outbox message.', [
                'topic' => $topic,
                'payload' => $payload,
                'exception' => $e,
            ]);

            throw new OutboxOperationException('Unable to enqueue outbox message.', 0, $e);
        }
    }

    public function moveToDlq(string $id, string $reason): void
    {
        try {
            $row = $this->data->fetchAssociative(
                'SELECT * FROM payment_outbox_message WHERE id = :id',
                ['id' => $id],
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to load outbox message for DLQ move.', ['id' => $id, 'exception' => $e]);

            throw new OutboxOperationException('Unable to read outbox message for DLQ move.', 0, $e);
        }

        if (false === $row) {
            $this->logger->warning('Outbox message not found for DLQ move.', ['id' => $id, 'reason' => $reason]);

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
            $this->logger->error('Failed to insert payment DLQ message.', ['id' => $id, 'reason' => $reason, 'exception' => $e]);

            throw new OutboxOperationException('Unable to insert DLQ message.', 0, $e);
        }

        try {
            $this->data->executeStatement(
                'DELETE FROM payment_outbox_message WHERE id = :id',
                ['id' => $id],
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to delete outbox message after DLQ move.', ['id' => $id, 'exception' => $e]);

            throw new OutboxOperationException('Unable to delete outbox message after DLQ move.', 0, $e);
        }
    }
}
