<?php
namespace OrderComponent\Payment\Tests\Payment\Unit\Reconciliation;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Contract\RepositoryInterface\Payment\PaymentRepositoryInterface;
use OrderComponent\Payment\Entity\Payment\Payment;
use OrderComponent\Payment\Service\Payment\Reconciliation\PaymentReconciliationService;
use PHPUnit\Framework\TestCase;

final class PaymentReconciliationServiceTest extends TestCase
{
    public function testOnFailedDoesNotThrowWhenPaymentMissing(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public function save(Payment $payment): void {}
            public function find(string $id): ?Payment { return null; }
        };
        $em = $this->createMock(EntityManagerInterface::class);
        $svc = new PaymentReconciliationService($repo, $em);
        $svc->onFailed('missing', 'declined', 'Card declined');
        $this->assertTrue(true);
    }
}
