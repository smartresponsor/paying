<?php
namespace OrderComponent\Payment\Service\Payment;

use OrderComponent\Payment\Entity\Payment\Payment;
use OrderComponent\Payment\Contract\RepositoryInterface\Payment\PaymentRepositoryInterface;

final class PaymentService
{
    public function __construct(private PaymentRepositoryInterface $repo) {}

    public function create(string $orderId, int $amountMinor, string $currency): Payment
    {
        $p = new Payment(\Ramsey\Uuid\Uuid::uuid4()->toString(), $orderId, $amountMinor, $currency);
        $this->repo->save($p);
        return $p;
    }
}
