<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Payment\Unit;
use App\Entity\Payment\Payment;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\Service\Payment\PaymentStartService;
use App\ServiceInterface\Payment\ProviderGuardInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentStartServiceTest extends TestCase
{
    public function testStartPersistsPaymentAndUpdatesStatus(): void
    {
        $repo = new class implements PaymentRepositoryInterface {
            public ?Payment $saved = null;
            public int $saveCount = 0;

            public function save(Payment $payment): void
            {
                $this->saved = $payment;
                ++$this->saveCount;
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

        $guard = new class implements ProviderGuardInterface {
            public array $receivedContext = [];

            public function start(string $provider, Payment $payment, array $context = []): array
            {
                $this->receivedContext = $context;

                return ['providerRef' => 'provider-ref-123'];
            }

            public function finalize(string $provider, Ulid $id, array $payload = []): Payment
            {
                throw new \RuntimeException('not-used');
            }

            public function refund(string $provider, Ulid $id, string $amount): Payment
            {
                throw new \RuntimeException('not-used');
            }

            public function reconcile(string $provider, Ulid $id): Payment
            {
                throw new \RuntimeException('not-used');
            }
        };

        $service = new PaymentStartService($guard, $repo);
        $started = $service->start('internal', '10.00', 'USD', '', 'payment-console');
        $payment = $started['payment'];

        self::assertSame(2, $repo->saveCount);
        self::assertSame($payment, $repo->saved);
        self::assertSame('processing', $payment->status()->value);
        self::assertSame('provider-ref-123', $started['providerRef']);
        self::assertSame('payment-console', $guard->receivedContext['origin']);
        self::assertArrayHasKey('idempotencyKey', $guard->receivedContext);
    }
}
