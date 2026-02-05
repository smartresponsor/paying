<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\ProjectionSyncInterface;
use App\InfrastructureInterface\Payment\PaymentProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;

class ProjectionSync implements ProjectionSyncInterface
{
    public function __construct(
        private readonly Connection $data,
        private readonly PaymentProjectionRepositoryInterface $infra
    ) {}

    public function sync(int $limit = 500): int
    {
        $wm = $this->watermark();
        $sql = 'SELECT id, amount, currency, status, updated_at FROM payment WHERE updated_at > :wm ORDER BY updated_at ASC LIMIT :lim';
        $rows = $this->data->fetchAllAssociative($sql, ['wm'=>$wm, 'lim'=>$limit]);
        $n = 0;
        foreach ($rows as $r) {
            $this->infra->upsert([
                'id' => (string)$r['id'],
                'amount' => (string)$r['amount'],
                'currency' => (string)$r['currency'],
                'status' => (string)$r['status'],
                'updated_at' => (string)$r['updated_at'],
            ]);
            $n++;
        }
        if ($n > 0) {
            $this->saveWatermark((string)end($rows)['updated_at']);
        }
        return $n;
    }

    public function rebuild(int $batch = 1000): int
    {
        $off = 0; $n=0;
        while (true) {
            $rows = $this->data->fetchAllAssociative(
                'SELECT id, amount, currency, status, updated_at FROM payment ORDER BY updated_at ASC LIMIT :lim OFFSET :off',
                ['lim'=>$batch, 'off'=>$off]
            );
            if (!$rows) break;
            foreach ($rows as $r) {
                $this->infra->upsert([
                    'id' => (string)$r['id'],
                    'amount' => (string)$r['amount'],
                    'currency' => (string)$r['currency'],
                    'status' => (string)$r['status'],
                    'updated_at' => (string)$r['updated_at'],
                ]);
                $n++;
            }
            $off += $batch;
            $this->saveWatermark((string)end($rows)['updated_at']);
        }
        return $n;
    }

    private function watermark(): string
    {
        $row = $this->data->fetchOne("SELECT value FROM payment_projection_meta WHERE name = 'watermark'");
        return $row ? (string)$row : '1970-01-01 00:00:00';
    }

    private function saveWatermark(string $ts): void
    {
        $this->data->executeStatement(
            "INSERT INTO payment_projection_meta(name, value) VALUES('watermark', :v)
             ON CONFLICT (name) DO UPDATE SET value=EXCLUDED.value",
            ['v'=>$ts]
        );
    }
}
