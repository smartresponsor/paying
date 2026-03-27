<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Reconciliation;

use App\Entity\Payment;
use App\Entity\PaymentRefund;
use App\Entity\PaymentTransaction;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\Reconciliation\PaymentReconciliationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentReconciliationService implements PaymentReconciliationServiceInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function onCaptured(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): Payment
    {
        $p = $this->requirePayment($paymentId);
        $p->markCompleted($gatewayTxId);

        $tx = new PaymentTransaction(
            (new Ulid())->toRfc4122(),
            (string) $p->id(),
            $gatewayTxId ?? 'captured',
            'capture',
            $amountMinor,
        );

        $this->em->persist($tx);
        $this->payments->save($p);

        return $p;
    }

    public function onRefunded(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): PaymentRefund
    {
        $p = $this->requirePayment($paymentId);
        $p->markRefunded($gatewayTxId);

        $refund = new PaymentRefund(
            (new Ulid())->toRfc4122(),
            (string) $p->id(),
            $amountMinor,
            $currency,
            $reason,
        );

        $this->em->persist($refund);
        $this->payments->save($p);
        $this->em->flush();

        return $refund;
    }

    public function onFailed(string $paymentId, string $errorCode, ?string $message = null): void
    {
        $p = $this->payments->find($paymentId);
        if ($p) {
            $p->markFailed('' !== $errorCode ? $errorCode : null);
            $this->payments->save($p);
        }
    }

    private function requirePayment(string $id): Payment
    {
        $p = $this->payments->find($id);
        if (!$p) {
            throw new \RuntimeException('Payment not found: '.$id);
        }

        return $p;
    }
}
