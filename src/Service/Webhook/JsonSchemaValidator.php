<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Webhook;

final class JsonSchemaValidator
{
    public function validate(array $payload, array $requiredKeys): bool
    {
        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $payload)) {
                return false;
            }
        }

        return true;
    }
}
