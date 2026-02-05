<?php
namespace OrderComponent\Payment\Service\Payment\Reconciliation;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Contract\RepositoryInterface\Payment\PaymentRepositoryInterface;
use OrderComponent\Payment\Entity\Payment\Payment;
use OrderComponent\Payment\Entity\Payment\PaymentTransaction;
use OrderComponent\Payment\Entity\Payment\PaymentRefund;

final class PaymentReconciliationService
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private EntityManagerInterface $em
    ) {}

    /** @return Payment */
    public function onCaptured(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null)
    {
        $p = $this->requirePayment($paymentId);
        $p->markCaptured();
        $tx = new PaymentTransaction(\Ramsey\Uuid\Uuid::uuid4()->toString(), $p->id(), $gatewayTxId ?? 'captured', 'capture', $amountMinor);
        $this->em->persist($tx);
        $this->payments->save($p);
        return $p;
    }

    /** @return PaymentRefund */
    public function onRefunded(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null)
    {
        $p = $this->requirePayment($paymentId);
        $refund = new PaymentRefund(\Ramsey\Uuid\Uuid::uuid4()->toString(), $p->id(), $amountMinor, $currency, $reason);
        $this->em->persist($refund);
        $this->em->flush();
        return $refund;
    }

    public function onFailed(string $paymentId, string $errorCode, ?string $message = null): void
    {
        $p = $this->payments->find($paymentId);
        if ($p) {
            $p->markFailed();
            $this->payments->save($p);
        }
    }

    private function requirePayment(string $id): Payment
    {
        $p = $this->payments->find($id);
        if (!$p) {
            throw new \RuntimeException('Payment not found: ' . $id);
        }
        return $p;
    }
}
