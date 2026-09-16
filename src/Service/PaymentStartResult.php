<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentEntity;

/**
 * Provides the payment start result service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentStartResult
{
    /** @param array<string, mixed> $providerResult */
    public function __construct(
        public PaymentEntity $payment,
        public ?string $providerRef,
        public array $providerResult,
    ) {
    }
}
