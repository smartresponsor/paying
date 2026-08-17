<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the provider router interface payment service boundary.
 */
interface PaymentProviderRouterInterface
{
    /**
     * Provides the for behavior for the provider router interface component.
     */
    public function for(string $provider): PaymentProviderInterface;
}
