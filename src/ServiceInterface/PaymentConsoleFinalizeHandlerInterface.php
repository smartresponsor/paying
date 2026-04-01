<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;

interface PaymentConsoleFinalizeHandlerInterface
{
    public function finalize(
        string $paymentId,
        string $provider,
        ?string $providerRef,
        ?string $gatewayTransactionId,
        ?string $status,
    ): ?Payment;
}
