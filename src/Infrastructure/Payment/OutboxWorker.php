<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use Doctrine\DBAL\Connection;

class OutboxWorker
{
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly Connection $data,
        private readonly PublisherTransportInterface $transport,
        private readonly OutboxPublisherInterface $outboxPublisher,
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

                if ($attempts >= self::MAX_ATTEMPTS) {
                    $this->outboxPublisher->moveToDlq($id, $reason);
                    continue;
                }

                $this->data->executeStatement(
                    'UPDATE payment_outbox_message SET status = :status, attempts = :attempts, last_error = :lastError WHERE id = :id',
                    [
                        'status' => 'failed',
                        'attempts' => $attempts,
                        'lastError' => $reason,
                        'id' => $id,
                    ],
                );
            }
        }

        return $count;
    }

    /** @return list<array<string, mixed>> */
    private function loadRows(int $limit, bool $retryFailed): array
    {
        $statuses = $retryFailed ? ['pending', 'failed'] : ['pending'];
        $sql = sprintf(
            'SELECT * FROM payment_outbox_message WHERE status IN (%s) ORDER BY occurred_at ASC LIMIT %d',
            implode(', ', array_map(fn (string $status): string => $this->quoteStatus($status), $statuses)),
            max(1, $limit),
        );

        return $this->data->fetchAllAssociative($sql);
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
