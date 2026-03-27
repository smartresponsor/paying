<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use App\ServiceInterface\ProjectionLagServiceInterface;
use Doctrine\DBAL\Connection;

final readonly class ProjectionLagService implements ProjectionLagServiceInterface
{
    public function __construct(
        private Connection $data,
        private PaymentProjectionRepositoryInterface $infra,
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function snapshot(): array
    {
        $dataUpdatedAt = (string) ($this->data->fetchOne('SELECT MAX(updated_at) FROM payment') ?: '');
        $infraUpdatedAt = (string) ($this->infra->maxUpdatedAt() ?: '');
        $lagMs = 0;

        if ('' !== $dataUpdatedAt && '' !== $infraUpdatedAt) {
            $lagMs = max(0, (strtotime($dataUpdatedAt) - strtotime($infraUpdatedAt)) * 1000);
        }

        return [
            'updatedAtData' => $dataUpdatedAt,
            'updatedAtInfra' => $infraUpdatedAt,
            'projectionLagMs' => $lagMs,
        ];
    }
}
