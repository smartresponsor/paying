<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\ControllerInterface\StatusControllerInterface;
use App\ServiceInterface\ProjectionLagServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class StatusController implements StatusControllerInterface
{
    public function __construct(
        private ProjectionLagServiceInterface $projectionLag,
        private LoggerInterface $logger,
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
        try {
            return new JsonResponse($this->projectionLag->snapshot(), Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to calculate payment status projection lag.', ['exception' => $e]);

            return new JsonResponse([
                'updatedAtData' => '',
                'updatedAtInfra' => '',
                'projectionLagMs' => 0,
            ], Response::HTTP_OK);
        }
    }
}
