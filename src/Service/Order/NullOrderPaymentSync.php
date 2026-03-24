<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Order;

use Psr\Log\LoggerInterface;

final class NullOrderPaymentSync implements OrderPaymentSyncInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onPaymentCaptured(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): void
    {
        $this->logger->info('Order payment sync: captured', compact('orderId', 'paymentId', 'amountMinor', 'currency', 'gatewayTxId'));
    }

    public function onPaymentRefunded(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): void
    {
        $this->logger->info('Order payment sync: refunded', compact('orderId', 'paymentId', 'amountMinor', 'currency', 'gatewayTxId', 'reason'));
    }

    public function onPaymentFailed(string $orderId, string $paymentId, string $errorCode, ?string $message = null): void
    {
        $this->logger->warning('Order payment sync: failed', compact('orderId', 'paymentId', 'errorCode', 'message'));
    }
}
