<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\Exception\OutboxOperationException;
use App\InfrastructureInterface\OutboxPublisherInterface;
use App\InfrastructureInterface\PublisherTransportInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class OutboxWorker
{
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly Connection $data,
        private readonly PublisherTransportInterface $transport,
        private readonly OutboxPublisherInterface $outboxPublisher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function run(int $limit = 100, bool $retryFailed = false): int
    {
        $rows = $this->loadRows($limit, $retryFailed);
        $count = 0;

        foreach ($rows as $row) {
            $payload = json_decode((string) $row['payload'], true) ?? [];
            $attempts = ((int) ($row['attempts'] ?? 0)) + 1;
            $routingKey = (string) ($row['routing_key'] ?? $row['type']);
            $id = (string) $row['id'];

            try {
                $this->transport->publish($routingKey, $payload);
                $this->data->executeStatement(
                    'UPDATE payment_outbox_message SET status = :status, attempts = :attempts, last_error = NULL WHERE id = :id',
                    [
                        'status' => 'published',
                        'attempts' => $attempts,
                        'id' => $id,
                    ],
                );
                ++$count;
            } catch (\Throwable $exception) {
                $reason = 'publish-failed: '.$exception->getMessage();
                $this->logger->error('Failed to publish outbox message.', [
                    'id' => $id,
                    'routingKey' => $routingKey,
                    'attempts' => $attempts,
                    'exception' => $exception,
                ]);

                if ($attempts >= self::MAX_ATTEMPTS) {
                    $this->outboxPublisher->moveToDlq($id, $reason);
                    continue;
                }

                try {
                    $this->data->executeStatement(
                        'UPDATE payment_outbox_message SET status = :status, attempts = :attempts, last_error = :lastError WHERE id = :id',
                        [
                            'status' => 'failed',
                            'attempts' => $attempts,
                            'lastError' => $reason,
                            'id' => $id,
                        ],
                    );
                } catch (Exception $e) {
                    $this->logger->error('Failed to persist outbox failure status.', [
                        'id' => $id,
                        'reason' => $reason,
                        'exception' => $e,
                    ]);

                    throw new OutboxOperationException('Unable to persist outbox failure state.', 0, $e);
                }
            }
        }

        return $count;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadRows(int $limit, bool $retryFailed): array
    {
        $statuses = $retryFailed ? ['pending', 'failed'] : ['pending'];
        $sql = sprintf(
            'SELECT * FROM payment_outbox_message WHERE status IN (%s) ORDER BY occurred_at ASC LIMIT %d',
            implode(', ', array_map(fn (string $status): string => $this->quoteStatus($status), $statuses)),
            max(1, $limit),
        );

        try {
            return $this->data->fetchAllAssociative($sql);
        } catch (Exception $e) {
            $this->logger->error('Failed to load outbox messages.', [
                'limit' => $limit,
                'retryFailed' => $retryFailed,
                'exception' => $e,
            ]);

            throw new OutboxOperationException('Unable to load outbox messages.', 0, $e);
        }
    }

    private function quoteStatus(string $status): string
    {
        $quoted = $this->data->quote($status);

        if (is_string($quoted) && '' !== $quoted) {
            return $quoted;
        }

        return "'".str_replace("'", "''", $status)."'";
    }
}
