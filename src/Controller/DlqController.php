<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\ControllerInterface\DlqControllerInterface;
use App\ServiceInterface\DlqServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DlqController implements DlqControllerInterface
{
    public function __construct(private readonly DlqServiceInterface $dlqService)
    {
    }

    #[RequireScope(['payment:admin'])]
    #[RequireScope(['payment:read'])]
    public function list(): JsonResponse
    {
        return new JsonResponse(['items' => $this->dlqService->list()], JsonResponse::HTTP_OK);
    }

    #[RequireScope(['payment:admin'])]
    public function replay(int $id): JsonResponse
    {
        if (!$this->dlqService->replay($id)) {
            return new JsonResponse(['ok' => false], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['ok' => true], JsonResponse::HTTP_OK);
    }
}
