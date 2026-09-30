<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Webhook;

/**
 * Provides the json schema validator step for webhook validation and normalization flows.
 */
final class PaymentJsonSchemaValidator
{
    /**
     * Validates the incoming payload for the validate workflow.
     */
    public function validate(array $payload, array $requiredKeys): bool
    {
        if (array_any($requiredKeys, fn ($requiredKey) => !array_key_exists($requiredKey, $payload))) {
            return false;
        }

        return true;
    }
}
