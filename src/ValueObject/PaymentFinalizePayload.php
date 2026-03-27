<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ValueObject;

final readonly class PaymentFinalizePayload
{
    public function __construct(
        private string $providerRef,
        private string $gatewayTransactionId,
        private string $status,
    ) {
    }

    /** @return array<string, string> */
    public function toProviderPayload(): array
    {
        return array_filter([
            'providerRef' => $this->providerRef,
            'gatewayTransactionId' => $this->gatewayTransactionId,
            'status' => $this->status,
        ], static fn (string $value): bool => '' !== $value);
    }
}
