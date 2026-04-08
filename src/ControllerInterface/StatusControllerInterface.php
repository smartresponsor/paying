<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines the status endpoint that reports payment projection freshness.
 */
interface StatusControllerInterface
{
    /**
     * Returns the current payment status and projection lag snapshot.
     */
    public function status(): JsonResponse;
}
