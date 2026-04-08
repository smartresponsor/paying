<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the HTTP create endpoint for payment aggregate creation.
 */
interface PaymentCreateControllerInterface
{
    /**
     * Creates a new payment aggregate from the validated request body.
     */
    public function create(Request $request): JsonResponse;
}
