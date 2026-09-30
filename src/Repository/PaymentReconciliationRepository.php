<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\Business\PaymentEntity;
use App\Paying\Entity\Business\PaymentRefundEntity;
use App\Paying\Entity\Business\PaymentTransactionEntity;
use App\Paying\RepositoryInterface\PaymentReconciliationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Owns Doctrine writes that atomically persist reconciliation evidence and payment state.
 */
final readonly class PaymentReconciliationRepository implements PaymentReconciliationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function saveCaptured(PaymentEntity $payment, PaymentTransactionEntity $transaction): void
    {
        $this->em->wrapInTransaction(function () use ($payment, $transaction): void {
            $this->em->persist($transaction);
            $this->em->persist($payment);
            $this->em->flush();
        });
    }

    public function saveRefunded(PaymentEntity $payment, PaymentRefundEntity $refund): void
    {
        $this->em->wrapInTransaction(function () use ($payment, $refund): void {
            $this->em->persist($refund);
            $this->em->persist($payment);
            $this->em->flush();
        });
    }
}
