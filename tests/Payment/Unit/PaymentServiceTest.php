<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Payment\Unit;

use App\Entity\Payment\Payment;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\Service\Payment\PaymentService;
use PHPUnit\Framework\TestCase;

final class PaymentServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public ?Payment $saved = null;

            public function save(Payment $payment): void
            {
                $this->saved = $payment;
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

        $service = new PaymentService($repo);
        $payment = $service->create('00000000-0000-0000-0000-000000000001', 1000, 'USD');

        self::assertInstanceOf(Payment::class, $payment);
        self::assertSame($payment, $repo->saved);
        self::assertSame('10.00', $payment->amount());
        self::assertSame('USD', $payment->currency());
    }
}
