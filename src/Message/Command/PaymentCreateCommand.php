<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Command;

final class PaymentCreateCommand
{
    /**
     * Canonical provider-oriented name for the create command.
     *
     * `gatewayCode` is intentionally preserved below as a backward-compatible alias
     * for older producers and named-argument call sites.
     */
    public string $providerCode;

    public function __construct(
        public string $orderId,
        public int $amountMinor,
        public string $currency,
        public string $gatewayCode,
        public ?string $idempotencyKey = null,
    ) {
        $this->providerCode = $gatewayCode;
    }

    public function canonicalProviderCode(): string
    {
        return '' !== trim($this->providerCode) ? $this->providerCode : $this->gatewayCode;
    }
}
