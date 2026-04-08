<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\ApiErrorResponseFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides the api error response factory service used by the payment lifecycle and operator-facing flows.
 */
final class ApiErrorResponseFactory implements ApiErrorResponseFactoryInterface
{
    /**
     * Provides the bad json body behavior for the api error response factory component.
     */
    public function badJsonBody(): JsonResponse
    {
        return new JsonResponse(['errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']]], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Provides the payment not found behavior for the api error response factory component.
     */
    public function paymentNotFound(): JsonResponse
    {
        return new JsonResponse(['error' => 'payment-not-found'], Response::HTTP_NOT_FOUND);
    }
}
