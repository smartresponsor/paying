<?php
namespace App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\Service\Order;

use OrderComponent\Payment\Contract\ServiceInterface\Order\OrderStatusPortInterface;
use Psr\Log\LoggerInterface;

final class NullOrderStatusPort implements OrderStatusPortInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function onPaymentCaptured(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): void
    {
        $this->logger->info('Order Port: captured', compact('orderId','paymentId','amountMinor','currency','gatewayTxId'));
    }

    public function onPaymentRefunded(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): void
    {
        $this->logger->info('Order Port: refunded', compact('orderId','paymentId','amountMinor','currency','gatewayTxId','reason'));
    }

    public function onPaymentFailed(string $orderId, string $paymentId, string $errorCode, ?string $message = null): void
    {
        $this->logger->warning('Order Port: failed', compact('orderId','paymentId','errorCode','message'));
    }
}
