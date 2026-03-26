<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Payment;
use App\Entity\PaymentWebhookLog;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentConsoleReadModel;
use App\ValueObject\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleReadModelTest extends TestCase
{
    public function testBuildFiltersPaymentsAndPrefillsSelectedCard(): void
    {
        $paymentA = new Payment(new Ulid('01HK153X000000000000000001'), PaymentStatus::processing, '10.00', 'USD');
        $paymentA->withProviderRef('stripe_pi_1001');

        $paymentB = new Payment(new Ulid('01HK153X000000000000000002'), PaymentStatus::completed, '25.00', 'USD');
        $paymentB->withProviderRef('internal_ref_2002');

        $logA = new PaymentWebhookLog('stripe', 'evt_1', ['paymentId' => (string) $paymentA->id()]);
        $logB = new PaymentWebhookLog('paypal', 'evt_2', ['paymentId' => (string) $paymentB->id()]);

        $repo = new class([$paymentA, $paymentB]) implements PaymentRepositoryInterface {
            /** @param list<Payment> $payments */
            public function __construct(private readonly array $payments)
            {
            }

            public function save(Payment $payment): void
            {
            }

            public function find(string $id): ?Payment
            {
                foreach ($this->payments as $payment) {
                    if ((string) $payment->id() === $id) {
                        return $payment;
                    }
                }

                return null;
            }

            public function listRecent(int $limit = 10): array
            {
                return array_slice($this->payments, 0, $limit);
            }

            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }
        };

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn(new class([$logA, $logB]) {
            /** @param list<PaymentWebhookLog> $logs */
            public function __construct(private readonly array $logs)
            {
            }

            public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null): array
            {
                return array_slice($this->logs, 0, $limit ?? count($this->logs));
            }
        });

        $readModel = new PaymentConsoleReadModel($repo, $entityManager);

        $result = $readModel->build('stripe', 'processing', (string) $paymentA->id());

        self::assertCount(1, $result['payments']);
        self::assertSame((string) $paymentA->id(), $result['payments'][0]['id']);
        self::assertNotNull($result['selectedPayment']);
        self::assertSame((string) $paymentA->id(), $result['selectedPayment']['id']);
        self::assertCount(1, $result['events']);
        self::assertSame('evt_1', $result['events'][0]['externalEventId']);
    }

    public function testBuildFallsBackToFirstFilteredPaymentWhenSelectionIsMissing(): void
    {
        $paymentA = new Payment(new Ulid('01HK153X000000000000000003'), PaymentStatus::processing, '10.00', 'USD');
        $paymentA->withProviderRef('stripe_pi_1003');

        $paymentB = new Payment(new Ulid('01HK153X000000000000000004'), PaymentStatus::processing, '25.00', 'USD');
        $paymentB->withProviderRef('stripe_pi_1004');

        $repo = new class([$paymentA, $paymentB]) implements PaymentRepositoryInterface {
            /** @param list<Payment> $payments */
            public function __construct(private readonly array $payments)
            {
            }

            public function save(Payment $payment): void
            {
            }

            public function find(string $id): ?Payment
            {
                foreach ($this->payments as $payment) {
                    if ((string) $payment->id() === $id) {
                        return $payment;
                    }
                }

                return null;
            }

            public function listRecent(int $limit = 10): array
            {
                return array_slice($this->payments, 0, $limit);
            }

            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }
        };

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn(new class {
            public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null): array
            {
                return [];
            }
        });

        $readModel = new PaymentConsoleReadModel($repo, $entityManager);
        $result = $readModel->build('', 'processing', '01HK153X000000000000000999');

        self::assertNotNull($result['selectedPayment']);
        self::assertSame((string) $paymentA->id(), $result['selectedPayment']['id']);
    }
}
