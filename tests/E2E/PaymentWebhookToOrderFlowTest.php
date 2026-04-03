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
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentWebhookToOrderFlowTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testWebhookCapturedGoesThroughOutboxAndConsumer(): void
    {
        $repo = new class {
            public array $storage = [];

            /**
             * @return __anonymous@1214
             */
            public function createQueryBuilder(string $alias): object
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly object $self)
                    {
                    }

                    /**
                     * @return __anonymous@1214
                     */
                    public function where(string $condition): self
                    {
                        return $this;
                    }

                    /**
                     * @return __anonymous@1214
                     */
                    public function orWhere(string $condition): self
                    {
                        return $this;
                    }

                    /**
                     * @return __anonymous@1214
                     */
                    public function setParameter(string $key, mixed $value): self
                    {
                        return $this;
                    }

                    /**
                     * @return __anonymous@1214
                     */
                    public function setMaxResults(int $limit): self
                    {
                        return $this;
                    }

                    /**
                     * @return __anonymous@2129
                     */
                    public function getQuery(): object
                    {
                        $self = $this->self;

                        return new class($self) {
                            public function __construct(private readonly object $self)
                            {
                            }

                            public function getResult(): array
                            {
                                return $this->self->storage;
                            }
                        };
                    }
                };
            }
        };

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        $em->method('flush')->willReturn(null);

        $outboxMessage = new PaymentOutboxMessage('11111111-1111-1111-1111-111111111111', 'payment.captured', [
            'paymentId' => 'pay_1',
            'orderId' => 'ord_1',
            'amountMinor' => 5000,
            'currency' => 'USD',
            'gatewayTransactionId' => 'gw_1',
        ], 'payment.captured');
        $repo->storage[] = $outboxMessage;

        $transport = new class implements TransportInterface {
            public array $envelopes = [];

            public function send(Envelope $envelope): Envelope
            {
                $this->envelopes[] = $envelope;

                return $envelope;
            }

            public function get(): iterable
            {
                return $this->envelopes;
            }

            public function ack(Envelope $envelope): void
            {
            }

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

            public function save(Payment $payment): void
            {
                $this->saved[] = $payment;
            }

            public function find(string $id): ?Payment
            {
                return 'pay_1' === $id ? $this->payment : null;
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
