<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the HTTP start endpoint for provider-backed payment flows.
 */
interface StartControllerInterface
{
    /**
     * Starts a payment flow from the validated HTTP request payload.
     */
    public function start(Request $request): JsonResponse;
}
