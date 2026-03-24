<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Attribute\Payment\RequireScope;
use App\Infrastructure\Payment\PaymentProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class StatusController implements StatusControllerInterface
{
    public function __construct(
        private readonly Connection $data,
        private readonly PaymentProjectionRepositoryInterface $infra,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[RequireScope(['payment:read'])]
    #[OA\Get(
        path: '/status',
        summary: 'Read payment projection freshness and data lag status.',
        tags: ['Payment'],
        responses: [
            new OA\Response(response: 200, description: 'Projection status.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:read scope.'),
        ],
    )]
    #[Security(name: 'Bearer')]
    public function status(): JsonResponse
    {
        $dataUpdatedAt = (string) ($this->data->fetchOne('SELECT MAX(updated_at) FROM payment') ?: '');
        $infraUpdatedAt = (string) ($this->infra->maxUpdatedAt() ?: '');
        $lagMs = 0;

        try {
            if ('' !== $dataUpdatedAt && '' !== $infraUpdatedAt) {
                $lagMs = max(0, (strtotime($dataUpdatedAt) - strtotime($infraUpdatedAt)) * 1000);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to calculate payment status projection lag.', ['exception' => $e]);
        }

        return new JsonResponse([
            'updatedAtData' => $dataUpdatedAt,
            'updatedAtInfra' => $infraUpdatedAt,
            'projectionLagMs' => $lagMs,
        ], JsonResponse::HTTP_OK);
    }
}
