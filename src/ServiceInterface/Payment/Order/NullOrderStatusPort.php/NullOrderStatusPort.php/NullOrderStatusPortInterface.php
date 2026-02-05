<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface NullOrderStatusPortInterface
{
    public function __construct(private LoggerInterface $logger);
    public function onPaymentCaptured(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): void;
    public function onPaymentRefunded(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): void;
    public function onPaymentFailed(string $orderId, string $paymentId, string $errorCode, ?string $message = null): void;
}
