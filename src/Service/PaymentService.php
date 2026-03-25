<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentServiceInterface;
use App\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

final class PaymentService implements PaymentServiceInterface
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
