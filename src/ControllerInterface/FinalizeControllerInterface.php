<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the HTTP finalize endpoint for existing payment aggregates.
 */
interface FinalizeControllerInterface
{
    /**
     * Finalizes an existing payment aggregate using the provider-specific finalize payload.
     */
    public function finalize(string $id, Request $request): JsonResponse;
}
