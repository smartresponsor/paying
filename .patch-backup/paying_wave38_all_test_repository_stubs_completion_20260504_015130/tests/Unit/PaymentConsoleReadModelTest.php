<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Entity\PaymentWebhookLogEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\Service\PaymentConsoleReadModel;
use App\Paying\ValueObject\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment console read model scenario within the payment unit test surface.
 */
final class PaymentConsoleReadModelTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that build filters payments and prefills selected card.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testBuildFiltersPaymentsAndPrefillsSelectedCard(): void
    {
        $paymentA = new PaymentEntity(new Ulid('01HK153X000000000000000001'), PaymentStatus::processing, '10.00', 'USD');
        $paymentA->withProviderRef('stripe_pi_1001');

        $paymentB = new PaymentEntity(new Ulid('01HK153X000000000000000002'), PaymentStatus::completed, '25.00', 'USD');
        $paymentB->withProviderRef('internal_ref_2002');

        $logA = new PaymentWebhookLogEntity('stripe', 'evt_1', ['paymentId' => (string) $paymentA->id()]);
        $logB = new PaymentWebhookLogEntity('paypal', 'evt_2', ['paymentId' => (string) $paymentB->id()]);

        $repo = new class([$paymentA, $paymentB]) implements PaymentRepositoryInterface {
            public function __construct(private readonly array $payments)
            {
            }

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(PaymentEntity $payment): void
            {
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?PaymentEntity
            {
                foreach ($this->payments as $payment) {
                    if ((string) $payment->id() === $id) {
                        return $payment;
                    }
                }

                return null;
            }

            /**
             * Implements the find by order id behavior required by the local test double used in this scenario.
             */
            public function findByOrderId(string $orderId): ?PaymentEntity
            {
                return null;
            }

            /**
             * Implements the list recent behavior required by the local test double used in this scenario.
             */
            public function listRecent(int $limit = 10): array
            {
                return array_slice($this->payments, 0, $limit);
            }

            /**
             * Implements the list ids by statuses behavior required by the local test double used in this scenario.
             */
            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }
        };

        $eventRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $eventRepository->method('findBy')->willReturn(array_slice([$logA, $logB], 0, 50));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($eventRepository);

        $readModel = new PaymentConsoleReadModel($repo, $entityManager);

        $result = $readModel->build('stripe', 'processing', (string) $paymentA->id());

        self::assertCount(1, $result['payments']);
        self::assertSame((string) $paymentA->id(), $result['payments'][0]['id']);
        self::assertNotNull($result['selectedPayment']);
        self::assertSame((string) $paymentA->id(), $result['selectedPayment']['id']);
        self::assertCount(1, $result['events']);
        self::assertSame('evt_1', $result['events'][0]['externalEventId']);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that build falls back to first filtered payment when selection is missing.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testBuildFallsBackToFirstFilteredPaymentWhenSelectionIsMissing(): void
    {
        $paymentA = new PaymentEntity(new Ulid('01HK153X000000000000000003'), PaymentStatus::processing, '10.00', 'USD');
        $paymentA->withProviderRef('stripe_pi_1003');

        $paymentB = new PaymentEntity(new Ulid('01HK153X000000000000000004'), PaymentStatus::processing, '25.00', 'USD');
        $paymentB->withProviderRef('stripe_pi_1004');

        $repo = new class([$paymentA, $paymentB]) implements PaymentRepositoryInterface {
            public function __construct(private readonly array $payments)
            {
            }

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(PaymentEntity $payment): void
            {
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?PaymentEntity
            {
                foreach ($this->payments as $payment) {
                    if ((string) $payment->id() === $id) {
                        return $payment;
                    }
                }

                return null;
            }

            /**
             * Implements the find by order id behavior required by the local test double used in this scenario.
             */
            public function findByOrderId(string $orderId): ?PaymentEntity
            {
                return null;
            }

            /**
             * Implements the list recent behavior required by the local test double used in this scenario.
             */
            public function listRecent(int $limit = 10): array
            {
                return array_slice($this->payments, 0, $limit);
            }

            /**
             * Implements the list ids by statuses behavior required by the local test double used in this scenario.
             */
            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }
        };

        $eventRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $eventRepository->method('findBy')->willReturn([]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($eventRepository);

        $readModel = new PaymentConsoleReadModel($repo, $entityManager);
        $result = $readModel->build('', 'processing', '01HK153X000000000000000999');

        self::assertNotNull($result['selectedPayment']);
        self::assertSame((string) $paymentA->id(), $result['selectedPayment']['id']);
    }
}
