<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Reconciliation;

use App\Entity\Payment;
use App\Entity\PaymentRefund;

interface PaymentReconciliationServiceInterface
{
    public function onCaptured(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): Payment;

    public function onRefunded(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): PaymentRefund;

    public function onFailed(string $paymentId, string $errorCode, ?string $message = null): void;
}
