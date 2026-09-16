<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines the contract for the api error response factory interface payment service boundary.
 */
interface PaymentApiErrorResponseFactoryInterface
{
    /**
     * Provides the bad json body behavior for the api error response factory interface component.
     */
    public function badJsonBody(): JsonResponse;

    /**
     * Provides the payment not found behavior for the api error response factory interface component.
     */
    public function paymentNotFound(): JsonResponse;
}
