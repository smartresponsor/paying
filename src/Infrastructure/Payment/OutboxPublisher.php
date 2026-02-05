<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use App\InfrastructureInterface\Payment\OutboxPublisherInterface;
use Doctrine\DBAL\Connection;

class OutboxPublisher implements OutboxPublisherInterface
{
    public function __construct(private readonly Connection $data) {}

    public function enqueue(string $topic, array $payload): void
    {
        $this->data->insert('payment_outbox', [
            'topic' => $topic,
            'payload' => json_encode($payload),
            'status' => 'pending',
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);
    }

    public function moveToDlq(int $id, string $reason): void
    {
        $row = $this->data->fetchAssociative('SELECT * FROM payment_outbox WHERE id = :id', ['id'=>$id]);
        if ($row) {
            $this->data->insert('payment_dlq', [
                'outbox_id' => $row['id'],
                'topic' => $row['topic'],
                'payload' => $row['payload'],
                'reason' => $reason,
                'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
            ]);
            $this->data->executeStatement('DELETE FROM payment_outbox WHERE id = :id', ['id'=>$id]);
        }
    }
}
