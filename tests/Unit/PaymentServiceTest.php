<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentService;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the payment service scenario within the payment unit test surface.
 */
final class PaymentServiceTest extends TestCase
{
    /**
     * Verifies that create.
     */
    public function testCreate(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public ?Payment $saved = null;

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(Payment $payment): void
            {
                $this->saved = $payment;
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?Payment
            {
                return null;
            }

            /**
             * Implements the find by order id behavior required by the local test double used in this scenario.
             */
            public function findByOrderId(string $orderId): ?Payment
            {
                return null;
            }

            /**
             * Implements the list recent behavior required by the local test double used in this scenario.
             */
            public function listRecent(int $limit = 10): array
            {
                return [];
            }

            /**
             * Implements the list ids by statuses behavior required by the local test double used in this scenario.
             */
            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }
        };

        $service = new PaymentService($repo);
        $payment = $service->create('00000000-0000-0000-0000-000000000001', 1000, 'usd');

        self::assertInstanceOf(Payment::class, $payment);
        self::assertSame($payment, $repo->saved);
        self::assertSame('10.00', $payment->amount());
        self::assertSame('USD', $payment->currency());
    }
}
