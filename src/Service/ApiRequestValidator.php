<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\ApiRequestValidatorInterface;
use App\ServiceInterface\ValidationErrorMapperInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiRequestValidator implements ApiRequestValidatorInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorMapperInterface $validationErrorMapper,
    ) {
    }

    public function validate(object $dto): ?JsonResponse
    {
        $violations = $this->validator->validate($dto);
        if (0 === count($violations)) {
            return null;
        }

        return new JsonResponse(['errors' => $this->validationErrorMapper->toArray($violations)], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
}
