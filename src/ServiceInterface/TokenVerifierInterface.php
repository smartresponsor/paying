<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the token verifier interface payment service boundary.
 */
interface TokenVerifierInterface
{
    /**
     * Verifies the input handled by the verify workflow.
     */
    public function verify(string $jwt): array;

    /**
     * Determines whether the has scopes condition is currently satisfied.
     */
    public function hasScopes(array $claims, array $required, bool $any = false): bool;
}
