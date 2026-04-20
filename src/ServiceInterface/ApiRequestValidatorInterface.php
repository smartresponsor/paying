<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines the contract for the api request validator interface payment service boundary.
 */
interface ApiRequestValidatorInterface
{
    /**
     * Validates the incoming payload for the validate workflow.
     */
    public function validate(object $dto): ?JsonResponse;
}
