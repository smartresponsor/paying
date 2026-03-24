<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Order;

interface OrderPaymentSyncInterface
{
    public function onPaymentCaptured(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): void;

    public function onPaymentRefunded(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): void;

    public function onPaymentFailed(string $orderId, string $paymentId, string $errorCode, ?string $message = null): void;
}
