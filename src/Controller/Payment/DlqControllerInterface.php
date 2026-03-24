<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Payment;

use Symfony\Component\HttpFoundation\JsonResponse;

interface DlqControllerInterface
{
    public function list(): JsonResponse;

    public function replay(int $id): JsonResponse;
}
