<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ControllerInterface\Payment;

use Symfony\Component\HttpFoundation\JsonResponse;

interface DlqControllerInterface
{
    public function list(): JsonResponse;
    public function replay(int $id): JsonResponse;
}
