<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Service\PaymentStartResult;

/**
 * Application service contract responsible for initiating payment flows.
 *
 * Implementations are expected to:
 * - create or reuse a payment aggregate
 * - delegate execution to a provider through the provider guard
 * - persist state transitions (new -> processing / failed)
 * - return a deterministic {@see PaymentStartResult}
 */
interface PaymentStartServiceInterface
{
    /**
     * Starts a new payment for the given order.
     *
     * @param string $orderId External order identifier.
     * @param string $provider Canonical provider code (internal|stripe|paypal).
     * @param string $amount Decimal amount in major units.
     * @param string $currency ISO-4217 currency code.
     * @param string $idempotencyKey Optional idempotency key for provider calls.
     * @param string $origin Logical origin of the call (api|console|messenger, etc.).
     *
     * @return PaymentStartResult Deterministic result containing updated payment and provider payload.
     */
    public function start(string $orderId, string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult;
}
