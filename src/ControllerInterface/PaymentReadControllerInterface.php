<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines the HTTP read endpoint for payment snapshots.
 */
interface PaymentReadControllerInterface
{
    /**
     * Returns the serialized payment snapshot for the requested identifier.
     */
    public function read(string $id): JsonResponse;
}
