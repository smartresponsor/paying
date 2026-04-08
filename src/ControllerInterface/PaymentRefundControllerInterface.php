<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the HTTP refund endpoint for existing payment aggregates.
 */
interface PaymentRefundControllerInterface
{
    /**
     * Applies a refund request to the targeted payment aggregate.
     */
    public function refund(string $id, Request $request): JsonResponse;
}
