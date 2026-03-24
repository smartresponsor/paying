<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment\Reconciliation;

use App\Entity\Payment\Payment;
use App\Entity\Payment\PaymentRefund;
use App\Entity\Payment\PaymentTransaction;
use App\Repository\Payment\PaymentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentReconciliationService
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private EntityManagerInterface $em,
    ) {
    }

    /** @return Payment */
    public function onCaptured(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null)
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

    /** @return PaymentRefund */
    public function onRefunded(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null)
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
