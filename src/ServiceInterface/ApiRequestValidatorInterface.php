<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

interface ApiRequestValidatorInterface
{
    public function validate(object $dto): ?JsonResponse;
}
