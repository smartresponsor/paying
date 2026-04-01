<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentServiceInterface;
use App\ValueObject\Money;
use App\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

final class PaymentService implements PaymentServiceInterface
{
    public function __construct(private readonly PaymentRepositoryInterface $paymentRepository)
    {
    }

    public function create(string $orderId, int $amountMinor, string $currency): Payment
    {
        $money = Money::fromMinor($amountMinor, strtoupper($currency));

        $payment = new Payment(new Ulid(), PaymentStatus::new, $money->toDecimalString(), $money->currency(), $orderId);

        $this->paymentRepository->save($payment);

        return $payment;
    }
}
