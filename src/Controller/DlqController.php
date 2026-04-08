<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\ControllerInterface\DlqControllerInterface;
use App\ServiceInterface\DlqServiceInterface;
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

    #[RequireScope(['payment:admin'])]
    #[RequireScope(['payment:read'])]
    /**
     * Returns the current dead-letter queue snapshot for payment operators.
     */
    public function list(): JsonResponse
    {
        return new JsonResponse(['items' => $this->dlqService->list()], Response::HTTP_OK);
    }

    #[RequireScope(['payment:admin'])]
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
