<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment\Webhook;

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
