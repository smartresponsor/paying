<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;

final readonly class PaymentStartResult
{
    /** @param array<string, mixed> $providerResult */
    public function __construct(
        public Payment $payment,
        public ?string $providerRef,
        public array $providerResult,
    ) {
    }
}
