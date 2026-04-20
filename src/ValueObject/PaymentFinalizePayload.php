<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ValueObject;

/**
 * Represents the payment finalize payload value object used by the payment lifecycle and related contracts.
 */
final readonly class PaymentFinalizePayload
{
    public function __construct(
        private string $providerRef,
        private string $providerTransactionId,
        private string $status,
    ) {
    }

    /**
     * Builds the normalized provider payload expected by finalize operations and outbound integrations.
     *
     * @return array<string, string>
     */
    public function toProviderPayload(): array
    {
        return array_filter([
            'providerRef' => $this->providerRef,
            'providerTransactionId' => $this->providerTransactionId,
            'status' => $this->status,
        ], static fn (string $value): bool => '' !== $value);
    }
}
