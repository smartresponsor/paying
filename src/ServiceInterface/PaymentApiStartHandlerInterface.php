<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Controller\Dto\PaymentStartRequestDto;

interface PaymentApiStartHandlerInterface
{
    /** @return array<string, mixed> */
    public function handle(PaymentStartRequestDto $dto, string $idempotencyKey, string $payloadHash): array;
}
