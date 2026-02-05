<?php
namespace OrderComponent\Payment\Message\Event\Payment;

final class PaymentRefundedEvent
{
    public function __construct(
        public string $paymentId,
        public int $amountMinor,
        public string $currency,
        public ?string $gatewayTransactionId = null,
        public ?string $reason = null
    ) {}
}
