<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface\Reconciliation;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Entity\PaymentRefundEntity;

/**
 * Defines the contract for the payment reconciliation service interface payment service boundary.
 */
interface PaymentReconciliationServiceInterface
{
    /**
     * Provides the on captured behavior for the payment reconciliation service interface component.
     */
    public function onCaptured(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): PaymentEntity;

    /**
     * Provides the on refunded behavior for the payment reconciliation service interface component.
     */
    public function onRefunded(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): PaymentRefundEntity;

    /**
     * Provides the on failed behavior for the payment reconciliation service interface component.
     */
    public function onFailed(string $paymentId, string $errorCode, ?string $message = null): void;
}
