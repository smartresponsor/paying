<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Payment\Unit\Reconciliation;

use App\Entity\Payment\Payment;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\Service\Payment\Reconciliation\PaymentReconciliationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class PaymentReconciliationServiceTest extends TestCase
{
    public function testOnFailedDoesNotThrowWhenPaymentMissing(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public function save(Payment $payment): void
            {
            }

            public function find(string $id): ?Payment
            {
                return null;
            }

            public function listRecent(int $limit = 10): array
            {
                return [];
            }

            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }
        };
        $em = $this->createMock(EntityManagerInterface::class);
        $svc = new PaymentReconciliationService($repo, $em);
        $svc->onFailed('missing', 'declined', 'Card declined');
        $this->assertTrue(true);
    }
}
