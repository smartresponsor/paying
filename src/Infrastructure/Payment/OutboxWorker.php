<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use App\InfrastructureInterface\Payment\PublisherTransportInterface;
use Doctrine\DBAL\Connection;

class OutboxWorker
{
    public function __construct(private readonly Connection $data, private readonly PublisherTransportInterface $transport) {}

    public function run(int $limit = 100): int
    {
        $rows = $this->data->fetchAllAssociative('SELECT * FROM payment_outbox WHERE status = :st ORDER BY id ASC LIMIT :lim', ['st'=>'pending','lim'=>$limit]);
        $count = 0;
        foreach ($rows as $r) {
            $payload = json_decode((string)$r['payload'], true) ?? [];
            try {
                $this->transport->publish((string)$r['topic'], $payload);
                $this->data->executeStatement('UPDATE payment_outbox SET status = :st WHERE id = :id', ['st'=>'sent','id'=>$r['id']]);
                $count++;
            } catch (\Throwable $e) {
                $this->data->insert('payment_dlq', [
                    'outbox_id' => $r['id'],
                    'topic' => $r['topic'],
                    'payload' => $r['payload'],
                    'reason' => 'publish-failed: '.$e->getMessage(),
                    'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                ]);
                $this->data->executeStatement('DELETE FROM payment_outbox WHERE id = :id', ['id'=>$r['id']]);
            }
        }
        return $count;
    }
}
