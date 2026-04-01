<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use App\ServiceInterface\ProjectionSyncInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

readonly class ProjectionSync implements ProjectionSyncInterface
{
    public function __construct(
        private Connection $data,
        private PaymentProjectionRepositoryInterface $infra,
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function sync(int $limit = 500): int
    {
        $wm = $this->infra->watermark() ?? '1970-01-01 00:00:00';
        $sql = 'SELECT id, order_id, amount, currency, status, provider_ref, updated_at FROM payment WHERE updated_at > :wm ORDER BY updated_at ASC LIMIT :lim';
        $rows = $this->data->fetchAllAssociative(
            $sql,
            ['wm' => $wm, 'lim' => $limit],
            ['wm' => ParameterType::STRING, 'lim' => ParameterType::INTEGER],
        );

        $n = 0;
        foreach ($rows as $r) {
            $this->infra->upsert([
                'id' => (string) $r['id'],
                'order_id' => (string) ($r['order_id'] ?? ''),
                'amount' => (string) $r['amount'],
                'currency' => (string) $r['currency'],
                'status' => (string) $r['status'],
                'provider_ref' => isset($r['provider_ref']) ? (string) $r['provider_ref'] : null,
                'updated_at' => (string) $r['updated_at'],
            ]);
            ++$n;
        }

        if ($n > 0) {
            $this->infra->saveWatermark(string(end($rows)['updated_at']));
        }

        return $n;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function rebuild(int $batch = 1000): int
    {
        $off = 0;
        $n = 0;
        while (true) {
            $rows = $this->data->fetchAllAssociative(
                'SELECT id, order_id, amount, currency, status, provider_ref, updated_at FROM payment ORDER BY updated_at ASC LIMIT :lim OFFSET :off',
                ['lim' => $batch, 'off' => $off],
                ['lim' => ParameterType::INTEGER, 'off' => ParameterType::INTEGER],
            );
            if (!$rows) {
                break;
            }
            foreach ($rows as $r) {
                $this->infra->upsert([
                    'id' => (string) $r['id'],
                    'order_id' => (string) ($r['order_id'] ?? ''),
                    'amount' => (string) $r['amount'],
                    'currency' => (string) $r['currency'],
                    'status' => (string) $r['status'],
                    'provider_ref' => isset($r['provider_ref']) ? (string) $r['provider_ref'] : null,
                    'updated_at' => (string) $r['updated_at'],
                ]);
                ++$n;
            }
            $off += $batch;
            $this->infra->saveWatermark(string(end($rows)['updated_at']));
        }

        return $n;
    }
}
