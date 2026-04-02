<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Command;

/**
 * Command used by the message bus to initiate a payment creation/start flow.
 *
 * The command preserves backward compatibility via {@see $gatewayCode} while
 * exposing {@see $providerCode} as the canonical provider-oriented field.
 */
final class PaymentCreateCommand
{
    /**
     * Canonical provider-oriented identifier.
     */
    public string $providerCode;

    /**
     * @param string $orderId External order identifier.
     * @param int $amountMinor Amount in minor units (e.g. cents).
     * @param string $currency ISO-4217 currency code.
     * @param string $gatewayCode Backward-compatible provider identifier.
     * @param ?string $idempotencyKey Optional idempotency key.
     */
    public function __construct(
        public string $orderId,
        public int $amountMinor,
        public string $currency,
        public string $gatewayCode,
        public ?string $idempotencyKey = null,
    ) {
        $this->providerCode = $gatewayCode;
    }

    /**
     * Returns the canonical provider identifier, resolving fallback when needed.
     */
    public function canonicalProviderCode(): string
    {
        return '' !== trim($this->providerCode) ? $this->providerCode : $this->gatewayCode;
    }
}
