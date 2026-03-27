<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\ApiErrorResponseFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiErrorResponseFactory implements ApiErrorResponseFactoryInterface
{
    public function badJsonBody(): JsonResponse
    {
        return new JsonResponse(['errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']]], Response::HTTP_BAD_REQUEST);
    }

    public function paymentNotFound(): JsonResponse
    {
        return new JsonResponse(['error' => 'payment-not-found'], Response::HTTP_NOT_FOUND);
    }
}
