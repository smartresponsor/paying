<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Order;

/**
 * Defines the contract for the order payment sync interface payment service boundary.
 */
interface OrderPaymentSyncInterface
{
    /**
     * Provides the on payment captured behavior for the order payment sync interface component.
     */
    public function onPaymentCaptured(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): void;

    /**
     * Provides the on payment refunded behavior for the order payment sync interface component.
     */
    public function onPaymentRefunded(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): void;

    /**
     * Provides the on payment failed behavior for the order payment sync interface component.
     */
    public function onPaymentFailed(string $orderId, string $paymentId, string $errorCode, ?string $message = null): void;
}
