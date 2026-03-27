<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\PaymentConsoleCreateHandlerInterface;
use App\ServiceInterface\PaymentServiceInterface;

final class PaymentConsoleCreateHandler implements PaymentConsoleCreateHandlerInterface
{
    public function __construct(private readonly PaymentServiceInterface $paymentService)
    {
    }

    public function create(string $orderId, int $amountMinor, string $currency): Payment
    {
        return $this->paymentService->create($orderId, $amountMinor, $currency);
    }
}
