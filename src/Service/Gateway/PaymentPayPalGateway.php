<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Gateway;

use App\Paying\ServiceInterface\Gateway\PaymentGatewayInterface;

/**
 * Implements the pay pal gateway integration surface for payment gateway operations.
 */
final class PaymentPayPalGateway implements PaymentGatewayInterface
{
    /**
     * Returns the provider metadata exposed by the code accessor.
     */
    public function code(): string
    {
        return 'paypal';
    }

    /**
     * Executes the authorize operation for the current payment workflow.
     */
    public function authorize(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'paypal_auth_'.$paymentId;
    }

    /**
     * Executes the capture operation for the current payment workflow.
     */
    public function capture(string $paymentId, int $amountMinor, string $currency): string
    {
        return 'paypal_capture_'.$paymentId;
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(string $paymentId, int $amountMinor, string $currency, ?string $reason = null): string
    {
        return 'paypal_refund_'.$paymentId;
    }
}
