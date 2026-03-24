<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Message\Handler\Payment;

use App\Message\Event\Payment\PaymentTransportMessage;
use App\Service\Order\OrderPaymentSyncInterface;
use App\Service\Payment\Reconciliation\PaymentReconciliationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'payment_events_in')]
final class PaymentEventConsumer
{
    public function __construct(
        private readonly PaymentReconciliationService $svc,
        private readonly ?OrderPaymentSyncInterface $orderPaymentSync = null,
    ) {
    }

    public function __invoke(PaymentTransportMessage $message): void
    {
        $type = trim($message->type);
        $payload = $message->payload;

        $paymentId = trim((string) ($payload['paymentId'] ?? ''));
        if ('' === $paymentId) {
            return;
        }

        $orderId = trim((string) ($payload['orderId'] ?? ''));
        $amount = (int) ($payload['amountMinor'] ?? 0);
        $currency = strtoupper((string) ($payload['currency'] ?? 'USD'));
        $gatewayTransactionId = isset($payload['gatewayTransactionId']) ? (string) $payload['gatewayTransactionId'] : null;
        $reason = isset($payload['reason']) ? (string) $payload['reason'] : null;
        $errorCode = trim((string) ($payload['errorCode'] ?? 'unknown'));
        $failureMessage = isset($payload['message']) ? (string) $payload['message'] : null;

        switch ($type) {
            case 'payment.captured':
                $this->svc->onCaptured($paymentId, $amount, $currency, $gatewayTransactionId);
                if ('' !== $orderId && null !== $this->orderPaymentSync) {
                    $this->orderPaymentSync->onPaymentCaptured($orderId, $paymentId, $amount, $currency, $gatewayTransactionId);
                }
                break;

            case 'payment.refunded':
                $this->svc->onRefunded($paymentId, $amount, $currency, $gatewayTransactionId, $reason);
                if ('' !== $orderId && null !== $this->orderPaymentSync) {
                    $this->orderPaymentSync->onPaymentRefunded($orderId, $paymentId, $amount, $currency, $gatewayTransactionId, $reason);
                }
                break;

            case 'payment.failed':
                $this->svc->onFailed($paymentId, $errorCode, $failureMessage);
                if ('' !== $orderId && null !== $this->orderPaymentSync) {
                    $this->orderPaymentSync->onPaymentFailed($orderId, $paymentId, $errorCode, $failureMessage);
                }
                break;
        }
    }
}
