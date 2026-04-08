<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\PaymentConsoleCreateHandlerInterface;
use App\ServiceInterface\PaymentServiceInterface;

/**
 * Provides the payment console create handler service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentConsoleCreateHandler implements PaymentConsoleCreateHandlerInterface
{
    public function __construct(private PaymentServiceInterface $paymentService)
    {
    }

    /**
     * Provides the create behavior for the payment console create handler component.
     */
    public function create(string $orderId, int $amountMinor, string $currency): Payment
    {
        return $this->paymentService->create($orderId, $amountMinor, $currency);
    }
}
