<?php
namespace OrderComponent\Payment\Tests\Unit;

use OrderComponent\Payment\Service\Payment\PaymentService;
use OrderComponent\Payment\Entity\Payment\Payment;
use OrderComponent\Payment\Contract\RepositoryInterface\Payment\PaymentRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class PaymentServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public ?Payment $saved = null;
            public function save(Payment $payment): void { $this->saved = $payment; }
            public function find(string $id): ?Payment { return null; }
        };

        $svc = new PaymentService($repo);
        $p = $svc->create('00000000-0000-0000-0000-000000000001', 1000, 'USD');
        $this->assertInstanceOf(Payment::class, $p);
        $this->assertSame(1000, $p->amountMinor());
        $this->assertSame('USD', $p->currency());
    }
}
