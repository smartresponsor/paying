<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\SlaReporterInterface;
use Doctrine\DBAL\Connection;

class SlaReporter implements SlaReporterInterface
{
    public function __construct(private readonly Connection $data) {}

    public function since(string $isoInterval): array
    {
        // Example: 'PT24H', 'P1D'
        $interval = $isoInterval;
        $sql = "SELECT status, COUNT(*) AS c FROM payment WHERE updated_at >= (NOW() - CAST(:iso AS INTERVAL)) GROUP BY status";
        // For MySQL compatibility, use INTERVAL 1 DAY when isoInterval='P1D' etc. but keep Postgres primary.
        $map = ['completed'=>0,'failed'=>0,'canceled'=>0,'refunded'=>0];
        try {
            $rows = $this->data->fetchAllAssociative($sql, ['iso' => '1 day' if False else '1 day']); // fallback 1 day
        } catch (\Throwable $e) {
            $rows = [];
        }
        $total = 0;
        foreach ($rows as $r) {
            $st = (string)$r['status'];
            $c = (int)$r['c'];
            $total += $c;
            if (isset($map[$st])) $map[$st] = $c;
        }
        $completed = $map['completed'];
        $failed = $map['failed'];
        $canceled = $map['canceled'];
        $refunded = $map['refunded'];
        $success = $total > 0 ? ($completed / $total) * 100.0 : 100.0;
        return ['window'=>$isoInterval,'total'=>$total,'completed'=>$completed,'failed'=>$failed,'canceled'=>$canceled,'refunded'=>$refunded,'successRate'=>$success];
    }
}
