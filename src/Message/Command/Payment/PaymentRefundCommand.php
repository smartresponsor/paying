<?php
namespace OrderComponent\Payment\Message\Command\Payment;

final class PaymentRefundCommand
{
    public function __construct(
        public string $paymentId,
        public int $amountMinor,
        public string $currency,
        public ?string $reason = null,
        public ?string $idempotencyKey = null
    ) {}
}