<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the oidc jwks cache interface payment service boundary.
 */
interface PaymentOidcJwksCacheInterface
{
    /**
     * Returns the value exposed by the get accessor.
     */
    public function get(): array;
}
