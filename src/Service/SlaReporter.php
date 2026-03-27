<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\SlaReporterInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

class SlaReporter implements SlaReporterInterface
{
    public function __construct(private readonly Connection $data, private readonly LoggerInterface $logger)
    {
    }

    /** @return array{window: string, total: int, completed: int, failed: int, canceled: int, refunded: int, successRate: float} */
    public function since(string $isoInterval): array
    {
        $interval = $isoInterval;
        $sql = 'SELECT status, COUNT(*) AS c FROM payment WHERE updated_at >= (NOW() - CAST(:iso AS INTERVAL)) GROUP BY status';
        $map = ['completed' => 0, 'failed' => 0, 'canceled' => 0, 'refunded' => 0];

        try {
            $rows = $this->data->fetchAllAssociative($sql, ['iso' => '1 day']);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to read payment SLA report rows.', ['exception' => $e, 'window' => $interval]);
            $rows = [];
        }

        $total = 0;
        foreach ($rows as $row) {
            $status = (string) $row['status'];
            $count = (int) $row['c'];
            $total += $count;
            if (isset($map[$status])) {
                $map[$status] = $count;
            }
        }

        $completed = $map['completed'];
        $failed = $map['failed'];
        $canceled = $map['canceled'];
        $refunded = $map['refunded'];
        $success = $total > 0 ? ($completed / $total) * 100.0 : 100.0;

        return [
            'window' => $isoInterval,
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'canceled' => $canceled,
            'refunded' => $refunded,
            'successRate' => $success,
        ];
    }
}
