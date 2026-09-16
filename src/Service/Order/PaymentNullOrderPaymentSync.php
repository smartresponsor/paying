<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Order;

use App\Paying\ServiceInterface\Order\OrderPaymentSyncInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides the null order payment sync service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentNullOrderPaymentSync implements OrderPaymentSyncInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Provides the on payment captured behavior for the null order payment sync component.
     */
    public function onPaymentCaptured(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): void
    {
        $this->logger->info('Order payment sync: captured', compact('orderId', 'paymentId', 'amountMinor', 'currency', 'gatewayTxId'));
    }

    /**
     * Provides the on payment refunded behavior for the null order payment sync component.
     */
    public function onPaymentRefunded(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): void
    {
        $this->logger->info('Order payment sync: refunded', compact('orderId', 'paymentId', 'amountMinor', 'currency', 'gatewayTxId', 'reason'));
    }

    /**
     * Provides the on payment failed behavior for the null order payment sync component.
     */
    public function onPaymentFailed(string $orderId, string $paymentId, string $errorCode, ?string $message = null): void
    {
        $this->logger->warning('Order payment sync: failed', compact('orderId', 'paymentId', 'errorCode', 'message'));
    }
}
