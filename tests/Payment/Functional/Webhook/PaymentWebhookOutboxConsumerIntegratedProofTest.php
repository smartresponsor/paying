<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Payment\Functional\Webhook;

use App\Controller\Webhook\PayPalWebhookController;
use App\Controller\Webhook\StripeWebhookController;
use App\Entity\Payment\Payment;
use App\Entity\Payment\PaymentOutboxMessage;
use App\Entity\Payment\PaymentWebhookLog;
use App\Message\Event\Payment\PaymentTransportMessage;
use App\Message\Handler\Payment\PaymentEventConsumer;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\Service\Order\OrderPaymentSyncInterface;
use App\Service\Payment\Outbox\PaymentOutboxProcessor;
use App\Service\Payment\Reconciliation\PaymentReconciliationService;
use App\Service\Payment\Webhook\JsonSchemaValidator;
use App\Service\Payment\Webhook\PayPalEventNormalizer;
use App\Service\Payment\Webhook\PayPalSignatureValidator;
use App\Service\Payment\Webhook\StripeEventNormalizer;
use App\Service\Payment\Webhook\StripeSignatureValidator;
use App\Service\Payment\WebhookVerifierInterface;
use App\ValueObject\Payment\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentWebhookOutboxConsumerIntegratedProofTest extends TestCase
{
    public function testStripeWebhookQueuesPublishesAndCapturesPayment(): void
    {
        $payment = new Payment(new Ulid(), PaymentStatus::new, '50.00', 'USD');
        $paymentId = $payment->id()->toRfc4122();
        $orderId = 'ord_stripe_1';

        [$em, $state] = $this->createWebhookEntityManager();
        $payments = $this->createPaymentRepository([$paymentId => $payment]);
        $orderSync = $this->createOrderSyncSpy();
        $transport = $this->createTransport();

        $controller = new StripeWebhookController(
            $em,
            new StripeSignatureValidator($this->createAlwaysValidVerifier()),
            new StripeEventNormalizer(),
            new JsonSchemaValidator(),
            new NullLogger(),
        );

        $payload = [
            'id' => 'evt_stripe_1',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_123',
                    'latest_charge' => 'ch_123',
                    'amount_received' => 5000,
                    'currency' => 'usd',
                    'metadata' => [
                        'payment' => $paymentId,
                        'order' => $orderId,
                    ],
                ],
            ],
        ];

        $request = new Request([], [], [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Stripe-Signature' => 't=1,v1=dummy',
        ], json_encode($payload, JSON_THROW_ON_ERROR));

        $response = $controller($request);
        self::assertSame(200, $response->getStatusCode());
        self::assertCount(1, $state->logs);
        self::assertCount(1, $state->outbox);
        self::assertSame('processed', $state->logs[0]->status());
        self::assertSame('payment.captured', $state->outbox[0]->type());
        self::assertSame($paymentId, $state->outbox[0]->payload()['paymentId'] ?? null);

        $processor = new PaymentOutboxProcessor($em, $transport, new NullLogger());
        self::assertSame(1, $processor->process(10, false));
        self::assertSame('published', $state->outbox[0]->status());
        self::assertCount(1, $transport->envelopes);

        $reconciliation = new PaymentReconciliationService($payments, $em);
        $consumer = new PaymentEventConsumer($reconciliation, $orderSync);

        foreach ($transport->envelopes as $envelope) {
            $message = $envelope->getMessage();
            self::assertInstanceOf(PaymentTransportMessage::class, $message);
            $consumer($message);
        }

        self::assertSame(PaymentStatus::completed, $payment->status());
        self::assertSame('ch_123', $payment->providerRef());
        self::assertSame([
            ['captured', $orderId, $paymentId, 5000, 'USD', 'ch_123'],
        ], $orderSync->events);
    }

    public function testPayPalWebhookQueuesPublishesAndRefundsPayment(): void
    {
        $payment = new Payment(new Ulid(), PaymentStatus::completed, '50.00', 'USD');
        $paymentId = $payment->id()->toRfc4122();
        $orderId = 'ord_paypal_1';

        [$em, $state] = $this->createWebhookEntityManager();
        $payments = $this->createPaymentRepository([$paymentId => $payment]);
        $orderSync = $this->createOrderSyncSpy();
        $transport = $this->createTransport();

        $controller = new PayPalWebhookController(
            $em,
            new PayPalSignatureValidator($this->createAlwaysValidVerifier()),
            new PayPalEventNormalizer(),
            new JsonSchemaValidator(),
            new NullLogger(),
        );

        $payload = [
            'id' => 'evt_paypal_1',
            'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
            'summary' => 'Refunded by buyer request',
            'resource' => [
                'id' => 'cap_123',
                'custom_id' => $paymentId,
                'status' => 'REFUNDED',
                'amount' => [
                    'value' => '50.00',
                    'currency_code' => 'usd',
                ],
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => $orderId,
                    ],
                ],
            ],
        ];

        $request = new Request([], [], [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_paypal-transmission-id' => 'tx_1',
        ], json_encode($payload, JSON_THROW_ON_ERROR));

        $response = $controller($request);
        self::assertSame(200, $response->getStatusCode());
        self::assertCount(1, $state->logs);
        self::assertCount(1, $state->outbox);
        self::assertSame('payment.refunded', $state->outbox[0]->type());
        self::assertSame($paymentId, $state->outbox[0]->payload()['paymentId'] ?? null);

        $processor = new PaymentOutboxProcessor($em, $transport, new NullLogger());
        self::assertSame(1, $processor->process(10, false));
        self::assertSame('published', $state->outbox[0]->status());
        self::assertCount(1, $transport->envelopes);

        $reconciliation = new PaymentReconciliationService($payments, $em);
        $consumer = new PaymentEventConsumer($reconciliation, $orderSync);

        foreach ($transport->envelopes as $envelope) {
            $message = $envelope->getMessage();
            self::assertInstanceOf(PaymentTransportMessage::class, $message);
            $consumer($message);
        }

        self::assertSame(PaymentStatus::refunded, $payment->status());
        self::assertSame('cap_123', $payment->providerRef());
        self::assertSame([
            ['refunded', $orderId, $paymentId, 5000, 'USD', 'cap_123', 'Refunded by buyer request'],
        ], $orderSync->events);
    }

    /**
     * @return array{0: EntityManagerInterface, 1: object{logs: array<int, PaymentWebhookLog>, outbox: array<int, PaymentOutboxMessage>}}
     */
    private function createWebhookEntityManager(): array
    {
        $state = new class {
            /** @var array<int, PaymentWebhookLog> */
            public array $logs = [];
            /** @var array<int, PaymentOutboxMessage> */
            public array $outbox = [];
        };

        $logRepo = new class($state) {
            public function __construct(private object $state)
            {
            }

            public function findOneBy(array $criteria): ?PaymentWebhookLog
            {
                foreach ($this->state->logs as $log) {
                    if ($log->provider() === ($criteria['provider'] ?? null)
                        && $log->externalEventId() === ($criteria['externalEventId'] ?? null)) {
                        return $log;
                    }
                }

                return null;
            }
        };

        $outboxRepo = new class($state) {
            public function __construct(private object $state)
            {
            }

            public function createQueryBuilder(string $alias): object
            {
                $state = $this->state;

                return new class($state) {
                    public function __construct(private object $state)
                    {
                    }

                    public function where(string $condition): self
                    {
                        return $this;
                    }

                    public function orWhere(string $condition): self
                    {
                        return $this;
                    }

                    public function setParameter(string $key, mixed $value): self
                    {
                        return $this;
                    }

                    public function setMaxResults(int $limit): self
                    {
                        return $this;
                    }

                    public function getQuery(): object
                    {
                        $state = $this->state;

                        return new class($state) {
                            public function __construct(private object $state)
                            {
                            }

                            public function getResult(): array
                            {
                                return $this->state->outbox;
                            }
                        };
                    }
                };
            }
        };

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('persist')->willReturnCallback(static function (object $entity) use ($state): void {
            if ($entity instanceof PaymentWebhookLog) {
                $state->logs[] = $entity;
            }
            if ($entity instanceof PaymentOutboxMessage) {
                $state->outbox[] = $entity;
            }
        });
        $em->method('flush')->willReturn(null);
        $em->method('getRepository')->willReturnCallback(static function (string $class) use ($logRepo, $outboxRepo): object {
            return match ($class) {
                PaymentWebhookLog::class => $logRepo,
                PaymentOutboxMessage::class => $outboxRepo,
                default => throw new \RuntimeException('Unexpected repository request: '.$class),
            };
        });

        return [$em, $state];
    }

    /** @param array<string, Payment> $storage */
    private function createPaymentRepository(array $storage): PaymentRepositoryInterface
    {
        return new class($storage) implements PaymentRepositoryInterface {
            /** @param array<string, Payment> $storage */
            public function __construct(private array $storage)
            {
            }

            public function save(Payment $payment): void
            {
                $this->storage[$payment->id()->toRfc4122()] = $payment;
            }

            public function find(string $id): ?Payment
            {
                return $this->storage[$id] ?? null;
            }

            public function listRecent(int $limit = 10): array
            {
                return array_slice(array_values($this->storage), 0, $limit);
            }

            public function listIdsByStatuses(array $statuses, int $limit = 100): array
            {
                $result = [];
                foreach ($this->storage as $id => $payment) {
                    if (in_array($payment->status()->value, $statuses, true)) {
                        $result[] = $id;
                    }
                }

                return array_slice($result, 0, $limit);
            }
        };
    }

    private function createAlwaysValidVerifier(): WebhookVerifierInterface
    {
        return new class implements WebhookVerifierInterface {
            public function verify(string $provider, string $raw, array $headers): bool
            {
                return true;
            }
        };
    }

    private function createOrderSyncSpy(): OrderPaymentSyncInterface
    {
        return new class implements OrderPaymentSyncInterface {
            /** @var list<array<int, string|int|null>> */
            public array $events = [];

            public function onPaymentCaptured(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null): void
            {
                $this->events[] = ['captured', $orderId, $paymentId, $amountMinor, $currency, $gatewayTxId];
            }

            public function onPaymentRefunded(string $orderId, string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null): void
            {
                $this->events[] = ['refunded', $orderId, $paymentId, $amountMinor, $currency, $gatewayTxId, $reason];
            }

            public function onPaymentFailed(string $orderId, string $paymentId, string $errorCode, ?string $message = null): void
            {
                $this->events[] = ['failed', $orderId, $paymentId, $errorCode, $message];
            }
        };
    }

    private function createTransport(): TransportInterface
    {
        return new class implements TransportInterface {
            /** @var list<Envelope> */
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
    }
}
