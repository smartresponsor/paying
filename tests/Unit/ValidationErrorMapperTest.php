<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\ValidationErrorMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Exercises the validation error mapper scenario within the payment unit test surface.
 */
final class ValidationErrorMapperTest extends TestCase
{
    /**
     * Verifies that to array maps violations to error payload.
     */
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

    /**
     * Verifies that to array returns stable order by field then message.
     */
    public function testToArrayReturnsStableOrderByFieldThenMessage(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('Second error on amount.', null, [], null, 'amount', null),
            new ConstraintViolation('Provider is required.', null, [], null, 'provider', null),
            new ConstraintViolation('First error on amount.', null, [], null, 'amount', null),
        ]);

        $mapper = new ValidationErrorMapper();
        $errors = $mapper->toArray($violations);

        self::assertSame([
            ['field' => 'amount', 'message' => 'First error on amount.'],
            ['field' => 'amount', 'message' => 'Second error on amount.'],
            ['field' => 'provider', 'message' => 'Provider is required.'],
        ], $errors);
    }
}
