<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\PaymentConsoleStartHandlerInterface;
use App\ServiceInterface\PaymentStartServiceInterface;

final class PaymentConsoleStartHandler implements PaymentConsoleStartHandlerInterface
{
    public function __construct(private readonly PaymentStartServiceInterface $paymentStartService)
    {
    }

    public function start(string $provider, string $amount, string $currency): Payment
    {
        $started = $this->paymentStartService->start($provider, $amount, $currency, '', 'payment-console');
        $payment = $started['payment'] ?? null;

        if (!$payment instanceof Payment) {
            throw new \RuntimeException('Payment start response does not contain payment entity.');
        }

        return $payment;
    }
}
