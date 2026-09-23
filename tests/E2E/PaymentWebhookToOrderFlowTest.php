<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\E2E;

use App\Paying\Entity\Business\PaymentEntity;
use App\Paying\Entity\Business\PaymentOutboxMessageEntity;
use App\Paying\Message\Event\PaymentTransportMessage;
use App\Paying\Message\Handler\PaymentEventConsumer;
use App\Paying\RepositoryInterface\PaymentOutboxRepositoryInterface;
use App\Paying\RepositoryInterface\PaymentReconciliationRepositoryInterface;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\Service\Order\PaymentNullOrderPaymentSync;
use App\Paying\Service\Outbox\PaymentOutboxProcessor;
use App\Paying\Service\Reconciliation\PaymentReconciliationService;
use App\Paying\ValueObject\PaymentStatus;
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
        $storage = [];
        $outboxMessage = new PaymentOutboxMessageEntity('7c4f1c2e-9e33-4c1b-9a6f-1a2b3c4d5011', 'payment.captured', [
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

        $outbox = $this->createMock(PaymentOutboxRepositoryInterface::class);
        $outbox->method('listProcessable')->willReturnCallback(static function () use (&$storage): array {
            return $storage;
        });
        $outbox->expects(self::once())->method('flush');

        $processor = new PaymentOutboxProcessor($outbox, $transport, new NullLogger());
        $published = $processor->process(10);
        self::assertSame(1, $published);
        self::assertSame('published', $outboxMessage->status());
        self::assertSame(1, $outboxMessage->attempts());

        $sync = new PaymentNullOrderPaymentSync(new NullLogger());
        $payment = new PaymentEntity(new Ulid('01HK153X000000000000000000'), PaymentStatus::processing, '50.00', 'USD');
        $saved = [];

        $payments = new class($payment, $saved) implements PaymentRepositoryInterface {
            public array $saved = [];

            public function __construct(private readonly PaymentEntity $payment, array $saved)
            {
                $this->saved = $saved;
            }

            /**
             * Implements the save behavior required by the local test double used in this scenario.
             */
            public function save(PaymentEntity $payment): void
            {
                $this->saved[] = $payment;
            }

            /**
             * Implements the find behavior required by the local test double used in this scenario.
             */
            public function find(string $id): ?PaymentEntity
            {
                return 'pay_1' === $id ? $this->payment : null;
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
                return [];
            }

            /**
             * Implements the list ids by statuses behavior required by the local test double used in this scenario.
             */
            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                return [];
            }

            public function listUpdatedAfter(\DateTimeImmutable $updatedAfter, int $limit = 500): array
            {
                throw new \LogicException('Test repository stub method is not configured: listUpdatedAfter');
            }

            public function listAllOrderedByUpdatedAt(int $limit = 1000, int $offset = 0): array
            {
                throw new \LogicException('Test repository stub method is not configured: listAllOrderedByUpdatedAt');
            }

            public function maxUpdatedAt(): ?string
            {
                throw new \LogicException('Test repository stub method is not configured: maxUpdatedAt');
            }

            public function countByStatusSince(\DateTimeImmutable $since): array
            {
                throw new \LogicException('Test repository stub method is not configured: countByStatusSince');
            }
        };

        $persisted = [];
        $reconciliationRepository = $this->createMock(PaymentReconciliationRepositoryInterface::class);
        $reconciliationRepository->expects(self::once())
            ->method('saveCaptured')
            ->willReturnCallback(static function (PaymentEntity $payment, object $transaction) use (&$persisted): void {
                $persisted[] = $transaction;
            });

        $reconciliation = new PaymentReconciliationService($payments, $reconciliationRepository);
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
