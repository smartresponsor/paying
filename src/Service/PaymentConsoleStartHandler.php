<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\PaymentConsoleStartHandlerInterface;
use App\ServiceInterface\PaymentStartServiceInterface;

/**
 * Provides the payment console start handler service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentConsoleStartHandler implements PaymentConsoleStartHandlerInterface
{
    public function __construct(private PaymentStartServiceInterface $paymentStartService)
    {
    }

    /**
     * Executes the start operation for the current payment workflow.
     */
    public function start(string $orderId, string $provider, string $amount, string $currency): Payment
    {
        return $this->paymentStartService->start($orderId, $provider, $amount, $currency, '', 'payment-console')->payment;
    }
}
