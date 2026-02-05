<?php
namespace OrderComponent\Payment\Message\Command\Payment;

final class PaymentCreateCommand
{
    public function __construct(
        public string $orderId,
        public int $amountMinor,
        public string $currency,
        public string $gatewayCode,
        public ?string $idempotencyKey = null
    ) {}
}