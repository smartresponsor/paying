<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\ServiceInterface\ApiRequestValidatorInterface;
use App\Paying\ServiceInterface\ValidationErrorMapperInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Provides the api request validator service used by the payment lifecycle and operator-facing flows.
 */
final readonly class ApiRequestValidator implements ApiRequestValidatorInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private ValidationErrorMapperInterface $validationErrorMapper,
    ) {
    }

    /**
     * Validates the incoming payload for the validate workflow.
     */
    public function validate(object $dto): ?JsonResponse
    {
        $violations = $this->validator->validate($dto);
        if (0 === count($violations)) {
            return null;
        }

        return new JsonResponse(['errors' => $this->validationErrorMapper->toArray($violations)], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
