<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use App\Paying\ServiceInterface\ProjectionLagServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Provides the projection lag service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class ProjectionLagService implements ProjectionLagServiceInterface
{
    public function __construct(
        private Connection $data,
        private PaymentProjectionRepositoryInterface $infra,
    ) {
    }

    /**
     * Executes the snapshot operation for the current payment workflow.
     *
     * @throws Exception
     */
    public function snapshot(): array
    {
        $dataUpdatedAt = (string) ($this->data->fetchOne('SELECT MAX(updated_at) FROM payment') ?: '');
        $infraUpdatedAt = $this->infra->maxUpdatedAt() ?: '';
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
