<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\ControllerInterface\DlqControllerInterface;
use App\ServiceInterface\DlqServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exposes operator endpoints for inspecting and replaying dead-lettered payment messages.
 */
final readonly class DlqController implements DlqControllerInterface
{
    public function __construct(private DlqServiceInterface $dlqService)
    {
    }

    #[RequireScope(['payment:admin', 'payment:read'], any: true)]
    #[OA\Get(
        path: '/payment/dlq',
        summary: 'List dead-lettered payment messages.',
        tags: ['Payment Operations'],
        responses: [
            new OA\Response(response: 200, description: 'Current dead-letter queue snapshot.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:admin or payment:read scope.'),
        ],
    )]
    #[Security(name: 'Bearer')]
    /**
     * Returns the current dead-letter queue snapshot for payment operators.
     */
    public function list(): JsonResponse
    {
        return new JsonResponse(['items' => $this->dlqService->list()], Response::HTTP_OK);
    }

    #[RequireScope(['payment:admin'])]
    #[OA\Post(
        path: '/payment/dlq/replay/{id}',
        summary: 'Replay a dead-lettered payment message.',
        tags: ['Payment Operations'],
        responses: [
            new OA\Response(response: 200, description: 'Replay accepted.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:admin scope.'),
            new OA\Response(response: 404, description: 'Dead-letter item not found.'),
        ],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[Security(name: 'Bearer')]
    /**
     * Replays a single dead-lettered message back into the payment processing flow.
     */
    public function replay(int $id): JsonResponse
    {
        if (!$this->dlqService->replay($id)) {
            return new JsonResponse(['ok' => false], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['ok' => true], Response::HTTP_OK);
    }
}
