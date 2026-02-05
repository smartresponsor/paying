<?php
namespace OrderComponent\Payment\Repository\Payment;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Entity\Payment\Payment;
use OrderComponent\Payment\Contract\RepositoryInterface\Payment\PaymentRepositoryInterface;

final class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(Payment $payment): void
    {
        $this->em->persist($payment);
        $this->em->flush();
    }

    public function find(string $id): ?Payment
    {
        return $this->em->getRepository(Payment::class)->find($id);
    }
}
