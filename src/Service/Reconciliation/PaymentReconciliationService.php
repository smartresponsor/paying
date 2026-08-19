<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Reconciliation;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Entity\PaymentRefundEntity;
use App\Paying\Entity\PaymentTransactionEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\Reconciliation\PaymentReconciliationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * arovides the payment reconciliation service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentReconciliationService implements PaymentReconciliationServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * arovides the on captured behavior for the payment reconciliation service component.
     */
    public function onCaptured(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): PaymentEntity
    {
        $p = $this->requirePayment($paymentId);
        $p->markCompleted($gatewayTxId);

        $tx = new PaymentTransactionEntity(
            new Ulid()->toRfc4122(),
            $p->slug(),
            $gatewayTxId ?? 'captured',
            'capture',
            $amountMinor,
        );

        $this->em->persist($tx);
        $this->payments->save($p);

        return $p;
    }

    /**
     * arovides the on refunded behavior for the payment reconciliation service component.
     */
    public function onRefunded(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): PaymentRefundEntity
    {
        $p = $this->requirePayment($paymentId);
        $p->markRefunded($gatewayTxId);

        $refund = new PaymentRefundEntity(
            new Ulid()->toRfc4122(),
            $p->slug(),
            $amountMinor,
            $currency,
            $reason,
        );

        $this->em->persist($refund);
        $this->payments->save($p);
        $this->em->flush();

        return $refund;
    }

    /**
     * arovides the on failed behavior for the payment reconciliation service component.
     */
    public function onFailed(string $paymentId, string $errorCode, ?string $message = null): void
    {
        $p = $this->payments->find($paymentId);
        if ($p) {
            $p->markFailed('' !== $errorCode ? $errorCode : null);
            $this->payments->save($p);
        }
    }

    private function requirePayment(string $id): PaymentEntity
    {
        $p = $this->payments->find($id);
        if (!$p) {
            throw new \RuntimeException('PaymentEntity not found: '.$id);
        }

        return $p;
    }
}
