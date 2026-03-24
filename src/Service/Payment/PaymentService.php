<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\ValueObject\Payment\PaymentStatus;
use Symfony\Component\Uid\Ulid;

final class PaymentService
{
    public function __construct(private PaymentRepositoryInterface $repo)
    {
    }

    public function create(string $orderId, int $amountMinor, string $currency): Payment
    {
        $p = new Payment(new Ulid(), PaymentStatus::new, number_format($amountMinor / 100, 2, '.', ''), $currency);
        $this->repo->save($p);

        return $p;
    }
}
