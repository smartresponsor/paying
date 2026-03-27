<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ValidationErrorMapperInterface
{
    /** @return array<int, array{field: string, message: string}> */
    public function toArray(ConstraintViolationListInterface $violations): array;
}
