<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\ApiRequestValidator;
use App\ServiceInterface\ValidationErrorMapperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiRequestValidatorTest extends TestCase
{
    private ValidatorInterface&MockObject $validator;

    private ValidationErrorMapperInterface&MockObject $validationErrorMapper;

    private ApiRequestValidator $service;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validationErrorMapper = $this->createMock(ValidationErrorMapperInterface::class);
        $this->service = new ApiRequestValidator($this->validator, $this->validationErrorMapper);
    }

    public function testValidateReturnsNullWhenDtoHasNoViolations(): void
    {
        $dto = new \stdClass();
        $violations = new ConstraintViolationList();

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($dto)
            ->willReturn($violations);

        $this->validationErrorMapper
            ->expects(self::never())
            ->method('toArray');

        self::assertNull($this->service->validate($dto));
    }

    /**
     * @throws \JsonException
     */
    /**
     * @throws \JsonException
     */
    public function testValidateReturnsUnprocessableEntityResponseWhenDtoHasViolations(): void
    {
        $dto = new \stdClass();
        $violations = new ConstraintViolationList([
            new ConstraintViolation('Amount is required.', null, [], null, 'amount', null),
        ]);
        $errors = [['field' => 'amount', 'message' => 'Amount is required.']];

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($dto)
            ->willReturn($violations);

        $this->validationErrorMapper
            ->expects(self::once())
            ->method('toArray')
            ->with($violations)
            ->willReturn($errors);

        $response = $this->service->validate($dto);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame(['errors' => $errors], json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }
}
