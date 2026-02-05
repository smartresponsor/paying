<?php
namespace OrderComponent\Payment\Message\Event\Payment;

final class PaymentFailedEvent
{
    public function __construct(
        public string $paymentId,
        public string $errorCode,
        public ?string $message = null
    ) {}
}
