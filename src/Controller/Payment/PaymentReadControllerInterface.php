<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Payment;

use Symfony\Component\HttpFoundation\JsonResponse;

interface PaymentReadControllerInterface
{
    public function read(string $id): JsonResponse;
}
