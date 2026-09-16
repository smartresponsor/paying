<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Entity\PaymentEntity;

/**
 * Defines the contract for the payment console finalize handler interface payment service boundary.
 */
interface PaymentConsoleFinalizeHandlerInterface
{
    /**
     * Executes the finalize operation for the current payment workflow.
     */
    public function finalize(
        string $paymentId,
        string $provider,
        ?string $providerRef,
        ?string $providerTransactionId,
        ?string $status,
    ): ?PaymentEntity;
}
