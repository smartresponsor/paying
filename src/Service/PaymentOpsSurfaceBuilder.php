<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\ServiceInterface\PaymentDlqServiceInterface;
use App\Paying\ServiceInterface\PaymentMetricInterface;
use App\Paying\ServiceInterface\PaymentProjectionLagServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class PaymentOpsSurfaceBuilder
{
    public function __construct(
        private PaymentMetricInterface $metrics,
        private PaymentProjectionLagServiceInterface $projectionLag,
        private PaymentDlqServiceInterface $dlqService,
        private LoggerInterface $logger,
    ) {
    }

    #[PaymentRequireScopeAttribute(['payment:read'])]
    #[OA\Get(
        path: '/metrics',
        summary: 'Render payment metrics and projection lag telemetry.',
        tags: ['PaymentEntity Operations'],
        responses: [
            new OA\Response(response: 200, description: 'Prometheus-style metrics payload.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:read scope.'),
        ],
    )]
    #[Security(name: 'Bearer')]
    public function metrics(): Response
    {
        $text = $this->metrics->export();

        try {
            $snapshot = $this->projectionLag->snapshot();
            $text .= sprintf("payment_projection_lag_ms %d\n", $snapshot['projectionLagMs']);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to calculate payment projection lag metrics.', ['exception' => $e]);
        }

        return new Response($text, Response::HTTP_OK, ['Content-Type' => 'text/plain; version=0.0.4']);
    }

    #[PaymentRequireScopeAttribute(['payment:read'])]
    #[OA\Get(
        path: '/status',
        summary: 'Read payment projection freshness and data lag status.',
        tags: ['PaymentEntity'],
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

    #[PaymentRequireScopeAttribute(['payment:admin', 'payment:read'], any: true)]
    #[OA\Get(
        path: '/payment/dlq',
        summary: 'List dead-lettered payment messages.',
        tags: ['PaymentEntity Operations'],
        responses: [
            new OA\Response(response: 200, description: 'Current dead-letter queue snapshot.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:admin or payment:read scope.'),
        ],
    )]
    #[Security(name: 'Bearer')]
    public function dlqList(): JsonResponse
    {
        return new JsonResponse(['items' => $this->dlqService->list()], Response::HTTP_OK);
    }

    #[PaymentRequireScopeAttribute(['payment:admin'])]
    #[OA\Post(
        path: '/payment/dlq/replay/{id}',
        summary: 'Replay a dead-lettered payment message.',
        tags: ['PaymentEntity Operations'],
        responses: [
            new OA\Response(response: 200, description: 'Replay accepted.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:admin scope.'),
            new OA\Response(response: 404, description: 'Dead-letter item not found.'),
        ],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[Security(name: 'Bearer')]
    public function dlqReplay(int $id): JsonResponse
    {
        if (!$this->dlqService->replay($id)) {
            return new JsonResponse(['ok' => false], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['ok' => true], Response::HTTP_OK);
    }
}
