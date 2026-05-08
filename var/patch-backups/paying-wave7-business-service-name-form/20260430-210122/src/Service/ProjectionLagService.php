<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use App\Paying\ServiceInterface\ProjectionLagServiceInterface;

/**
 * Provides the projection lag service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class ProjectionLagService implements ProjectionLagServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private PaymentProjectionRepositoryInterface $paymentProjectionRepository,
    ) {
    }

    /**
     * Executes the snapshot operation for the current payment workflow.
     */
    public function snapshot(): array
    {
        $dataUpdatedAt = $this->payments->maxUpdatedAt() ?? '';
        $infraUpdatedAt = $this->paymentProjectionRepository->maxUpdatedAt() ?: '';
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
