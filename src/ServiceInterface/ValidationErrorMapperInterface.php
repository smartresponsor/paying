<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Defines the contract for the validation error mapper interface payment service boundary.
 */
interface ValidationErrorMapperInterface
{
    /**
     * Provides the to array behavior for the validation error mapper interface component.
     */
    public function toArray(ConstraintViolationListInterface $violations): array;
}
