<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\Payment;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentServiceInterface;
use App\Paying\ValueObject\Money;
use App\Paying\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the payment service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentService implements PaymentServiceInterface
{
    public function __construct(private PaymentRepositoryInterface $paymentRepository)
    {
    }

    /**
     * Provides the create behavior for the payment service component.
     */
    public function create(string $orderId, int $amountMinor, string $currency): Payment
    {
        $money = Money::fromMinor($amountMinor, strtoupper($currency));

        $payment = new Payment(new Ulid(), PaymentStatus::new, $money->toDecimalString(), $money->currency(), $orderId);

        $this->paymentRepository->save($payment);

        return $payment;
    }
}
