<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

interface DlqControllerInterface
{
    public function list(): JsonResponse;

    public function replay(int $id): JsonResponse;
}
