<?php
namespace OrderComponent\Payment\Contract\RepositoryInterface\Payment;

use OrderComponent\Payment\Entity\Payment\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment): void;
    public function find(string $id): ?Payment;
}
