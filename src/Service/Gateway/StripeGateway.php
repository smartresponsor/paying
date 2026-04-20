<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Gateway;

use App\Paying\ServiceInterface\Gateway\PaymentGatewayInterface;

/**
 * Implements the stripe gateway integration surface for payment gateway operations.
 */
final class StripeGateway implements PaymentGatewayInterface
{
    /**
     * Returns the provider metadata exposed by the code accessor.
     */
    public function code(): string
    {
        return 'stripe';
    }

    /**
     * Executes the authorize operation for the current payment workflow.
     */
    public function authorize(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'stripe_auth_'.$paymentId;
    }

    /**
     * Executes the capture operation for the current payment workflow.
     */
    public function capture(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'stripe_capture_'.$paymentId;
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string
    {
        return 'stripe_refund_'.$paymentId;
    }
}
