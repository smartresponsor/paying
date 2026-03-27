<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\ValidationErrorMapperInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationErrorMapper implements ValidationErrorMapperInterface
{
    public function toArray(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => (string) $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        usort($errors, static function (array $left, array $right): int {
            $byField = strcmp($left['field'], $right['field']);
            if (0 !== $byField) {
                return $byField;
            }

            return strcmp($left['message'], $right['message']);
        });

        return $errors;
    }
}
