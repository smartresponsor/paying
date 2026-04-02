<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;

/**
 * Immutable result object returned by the payment start orchestration.
 *
 * It captures the persisted payment aggregate together with the provider-side
 * reference and the raw provider payload returned by the active provider.
 * The result is intentionally small so it can be reused by API, console, and
 * message-based start flows without leaking transport-specific concerns.
 */
final readonly class PaymentStartResult
{
    /**
     * @param Payment $payment Persisted payment aggregate after the start attempt.
     * @param ?string $providerRef Canonical provider reference resolved during start, if any.
     * @param array<string, mixed> $providerResult Raw provider response payload returned by the provider adapter.
     */
    public function __construct(
        public Payment $payment,
        public ?string $providerRef,
        public array $providerResult,
    ) {
    }
}
