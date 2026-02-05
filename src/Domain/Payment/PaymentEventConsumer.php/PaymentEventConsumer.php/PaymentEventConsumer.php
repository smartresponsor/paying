<?php
namespace App\Domain\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\Domain\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\Message\Handler\Payment;

use OrderComponent\Payment\Service\Payment\Reconciliation\PaymentReconciliationService;
use OrderComponent\Payment\Contract\ServiceInterface\Order\OrderStatusPortInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'payment_events_in')]
final class PaymentEventConsumer
{
    public function __construct(
        private PaymentReconciliationService $svc,
        private ?OrderStatusPortInterface $orderPort = null
    ) {}

    public function __invoke(object $message): void
    {
        $type = (string)($message->type ?? '');
        $payload = (array)($message->payload ?? []);

        $paymentId = (string)($payload['paymentId'] ?? '');
        $orderId = (string)($payload['orderId'] ?? '');
        $amount = (int)($payload['amountMinor'] ?? 0);
        $currency = (string)($payload['currency'] ?? 'USD');
        $gw = $payload['gatewayTransactionId'] ?? null;
        $reason = $payload['reason'] ?? null;
        $err = (string)($payload['errorCode'] ?? 'unknown');
        $msg = $payload['message'] ?? null;

        if ($paymentId === '') { return; }

        switch ($type) {
            case 'payment.captured':
                $this->svc->onCaptured($paymentId, $amount, $currency, $gw);
                if ($orderId !== '' && $this->orderPort) {
                    $this->orderPort->onPaymentCaptured($orderId, $paymentId, $amount, $currency, $gw);
                }
                break;
            case 'payment.refunded':
                $this->svc->onRefunded($paymentId, $amount, $currency, $gw, $reason);
                if ($orderId !== '' && $this->orderPort) {
                    $this->orderPort->onPaymentRefunded($orderId, $paymentId, $amount, $currency, $gw, $reason);
                }
                break;
            case 'payment.failed':
                $this->svc->onFailed($paymentId, $err, $msg);
                if ($orderId !== '' && $this->orderPort) {
                    $this->orderPort->onPaymentFailed($orderId, $paymentId, $err, $msg);
                }
                break;
        }
    }
}
