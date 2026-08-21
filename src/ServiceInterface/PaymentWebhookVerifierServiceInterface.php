<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the webhook verifier interface payment service boundary.
 */
interface PaymentWebhookVerifierServiceInterface
{
    /**
     * Verifies the input handled by the verify workflow.
     */
    public function verify(string $provider, string $raw, array $headers): bool;
}
