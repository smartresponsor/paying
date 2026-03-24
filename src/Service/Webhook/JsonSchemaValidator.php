<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Webhook;

final class JsonSchemaValidator
{
    /** Very light validator: checks presence of required keys */
    public function validate(array $payload, array $requiredKeys): bool
    {
        foreach ($requiredKeys as $k) {
            if (!array_key_exists($k, $payload)) {
                return false;
            }
        }

        return true;
    }
}
