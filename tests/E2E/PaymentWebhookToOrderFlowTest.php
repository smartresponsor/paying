<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\E2E;

use App\Entity\Payment;
use App\Entity\PaymentOutboxMessage;
use App\Message\Event\PaymentTransportMessage;
use App\Message\Handler\PaymentEventConsumer;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\Order\NullOrderPaymentSync;
use App\Service\Outbox\PaymentOutboxProcessor;
use App\Service\Reconciliation\PaymentReconciliationService;
use App\ValueObject\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment webhook to order flow scenario within the payment e2e test surface.
 */
final class PaymentWebhookToOrderFlowTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that webhook captured goes through outbox and consumer.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testWebhookCapturedGoesThroughOutboxAndConsumer(): void
    {
        $query = $this->createMock(Query::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $repository = $this->createMock(EntityRepository::class);
        $storage = [];

        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('orWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $repository->method('createQueryBuilder')->willReturn($queryBuilder);
        $query->method('getResult')->willReturnCallback(static function () use (&$storage): array {
            return $storage;
        });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $outboxMessage = new PaymentOutboxMessage('11111111-1111-1111-1111-111111111111', 'payment.captured', [
            'paymentId' => 'pay_1',
            'orderId' => 'ord_1',
            'amountMinor' => 5000,
            'currency' => 'USD',
            'gatewayTransactionId' => 'gw_1',
        ], 'payment.captured');
        $storage[] = $outboxMessage;

        $transport = new class implements TransportInterface {
            public array $envelopes = [];

            /**
             * Implements the send behavior required by the local test double used in this scenario.
             */
            public function send(Envelope $envelope): Envelope
            {
                $this->envelopes[] = $envelope;

                return $envelope;
            }

            /**
             * Implements the get behavior required by the local test double used in this scenario.
             */
            public function get(): iterable
            {
                return $this->envelopes;
            }

            /**
             * Implements the ack behavior required by the local test double used in this scenario.
             */
            public function ack(Envelope $envelope): void
            {
            }

            /**
             * Implements the reject behavior required by the local test double used in this scenario.
             */
            public function reject(Envelope $envelope): void
            {
            }
        };

        $processor = new PaymentOutboxProcessor($em, $transport, new NullLogger());
        $published = $processor->process(10);
        self::assertSame(1, $published);
        self::assertSame('published', $outboxMessage->status());
        self::assertSame(1, $outboxMessage->attempts());

        $sync = new NullOrderPaymentSync(new NullLogger());
        $payment = new Payment(new Ulid('01HK153X000000000000000000'), PaymentStatus::processing, '50.00', 'USD');
        $saved = [];

        $payments = new class($payment, $saved) implements PaymentRepositoryInterface {
            public array $saved = [];

            public function __construct(private readonly Payment $payment, array $saved)
            {
                $this->saved = $saved;
            }

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(Payment $payment): void
            {
                $this->saved[] = $payment;
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?Payment
            {
                return 'pay_1' === $id ? $this->payment : null;
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

        $persisted = [];
        $reconciliationEm = $this->createMock(EntityManagerInterface::class);
        $reconciliationEm->expects(self::once())
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persisted): void {
                $persisted[] = $entity;
            });

        $reconciliation = new PaymentReconciliationService($payments, $reconciliationEm);
        $consumer = new PaymentEventConsumer($reconciliation, $sync);

        foreach ($transport->envelopes as $envelope) {
            $message = $envelope->getMessage();
            self::assertInstanceOf(PaymentTransportMessage::class, $message);
            $consumer($message);
        }

        self::assertSame(PaymentStatus::completed, $payment->status());
        self::assertSame('gw_1', $payment->providerRef());
        self::assertCount(1, $payments->saved);
        self::assertCount(1, $persisted);
    }
}
