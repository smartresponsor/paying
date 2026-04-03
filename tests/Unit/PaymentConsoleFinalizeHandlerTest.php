<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentConsoleFinalizeHandler;
use App\ServiceInterface\ProviderGuardInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleFinalizeHandlerTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFinalizeReturnsNullWhenPaymentDoesNotExist(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public function save(Payment $payment): void
            {
            }

            public function find(string $id): ?Payment
            {
                return null;
            }

            public function findByOrderId(string $orderId): ?Payment
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

        $guard = $this->createMock(ProviderGuardInterface::class);
        $guard->expects(self::never())->method('finalize');

        $handler = new PaymentConsoleFinalizeHandler($repo, $guard);

        self::assertNull($handler->finalize((string) new Ulid(), 'internal', null, null, null));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFinalizeUpdatesAndPersistsPayment(): void
    {
        $existing = new Payment(new Ulid(), PaymentStatus::new, '10.00', 'USD');
        $resolved = new Payment(new Ulid(), PaymentStatus::completed, '10.00', 'USD');

        $repo = new class($existing) implements PaymentRepositoryInterface {
            public ?Payment $saved = null;

            public function __construct(private readonly Payment $existing)
            {
            }

            public function save(Payment $payment): void
            {
                $this->saved = $payment;
            }

            public function find(string $id): ?Payment
            {
                return $this->existing;
            }

            public function findByOrderId(string $orderId): ?Payment
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

        $guard = $this->createMock(ProviderGuardInterface::class);
        $guard
            ->expects(self::once())
            ->method('finalize')
            ->with(
                'internal',
                self::isInstanceOf(Ulid::class),
                ['providerRef' => 'ref-1', 'providerTransactionId' => 'gw-1', 'status' => 'completed'],
            )
            ->willReturn($resolved);

        $handler = new PaymentConsoleFinalizeHandler($repo, $guard);
        $result = $handler->finalize((string) new Ulid(), 'internal', 'ref-1', 'gw-1', 'completed');

        self::assertSame($existing, $result);
        self::assertSame(PaymentStatus::completed, $result?->status());
        self::assertSame($existing, $repo->saved);
    }
}
