<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines the dead-letter queue management endpoints exposed by the payment module.
 */
interface DlqControllerInterface
{
    /**
     * Returns the current dead-letter queue snapshot for operator review.
     */
    public function list(): JsonResponse;

    /**
     * Replays a single dead-lettered entry back into the processing pipeline.
     */
    public function replay(int $id): JsonResponse;
}
