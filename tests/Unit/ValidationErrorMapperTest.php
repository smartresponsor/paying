<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\ValidationErrorMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class ValidationErrorMapperTest extends TestCase
{
    public function testToArrayMapsViolationsToErrorPayload(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('Amount is required.', null, [], null, 'amount', null),
            new ConstraintViolation('Currency is invalid.', null, [], null, 'currency', 'US'),
        ]);

        $mapper = new ValidationErrorMapper();
        $errors = $mapper->toArray($violations);

        self::assertSame([
            ['field' => 'amount', 'message' => 'Amount is required.'],
            ['field' => 'currency', 'message' => 'Currency is invalid.'],
        ], $errors);
    }
}
