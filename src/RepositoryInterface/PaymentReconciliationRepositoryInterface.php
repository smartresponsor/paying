<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\RepositoryInterface;

use App\Paying\Entity\Business\PaymentEntity;
use App\Paying\Entity\Business\PaymentRefundEntity;
use App\Paying\Entity\Business\PaymentTransactionEntity;

/**
 * Defines atomic persistence for reconciliation records and their payment state.
 */
interface PaymentReconciliationRepositoryInterface
{
    public function saveCaptured(PaymentEntity $payment, PaymentTransactionEntity $transaction): void;

    public function saveRefunded(PaymentEntity $payment, PaymentRefundEntity $refund): void;
}
