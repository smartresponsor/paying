<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\ApiErrorResponseFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiErrorResponseFactory implements ApiErrorResponseFactoryInterface
{
    public function badJsonBody(): JsonResponse
    {
        return new JsonResponse(['errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']]], JsonResponse::HTTP_BAD_REQUEST);
    }

    public function paymentNotFound(): JsonResponse
    {
        return new JsonResponse(['error' => 'payment-not-found'], JsonResponse::HTTP_NOT_FOUND);
    }
}
