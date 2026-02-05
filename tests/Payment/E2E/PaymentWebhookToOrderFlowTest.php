<?php
namespace OrderComponent\Payment\Tests\Payment\E2E;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Entity\Payment\PaymentOutboxMessage;
use OrderComponent\Payment\Service\Payment\Outbox\PaymentOutboxProcessor;
use OrderComponent\Payment\Message\Handler\Payment\PaymentEventConsumer;
use OrderComponent\Payment\Service\Order\NullOrderStatusPort;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class PaymentWebhookToOrderFlowTest extends TestCase
{
    public function testWebhookCaptured_GoesThroughOutboxAndConsumer(): void
    {
        // 1) emulate DB repository + EM for outbox
        $messages = [];
        $repo = new class($messages) {
            public array $storage = [];
            public function createQueryBuilder($alias) {
                $self = $this;
                return new class($self) {
                    private $self;
                    public function __construct($self) { $this->self = $self; }
                    public function where($cond) { return $this; }
                    public function orWhere($cond) { return $this; }
                    public function setParameter($k,$v){ return $this; }
                    public function setMaxResults($n){ return $this; }
                    public function getQuery(){ 
                        $self = $this->self;
                        return new class($self) {
                            private $self;
                            public function __construct($self){ $this->self=$self; }
                            public function getResult(){ return $this->self->storage; }
                        };
                    }
                };
            }
        };
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        $em->method('flush')->willReturn(null);
        $em->method('persist')->willReturnCallback(function($entity) use ($repo) {
            if ($entity instanceof PaymentOutboxMessage) {
                $repo->storage[] = $entity;
            }
        });

        // Prepare one outbox message like after webhook normalization
        $out = new PaymentOutboxMessage('11111111-1111-1111-1111-111111111111', 'payment.captured', [
            'paymentId' => 'pay_1', 'orderId' => 'ord_1', 'amountMinor' => 5000, 'currency' => 'USD', 'gatewayTransactionId' => 'gw_1'
        ], 'payment.captured');
        $repo->storage[] = $out;

        // 2) InMemory transport that collects envelopes and passes to consumer manually
        class InMemoryTransport implements TransportInterface {
            public array $envelopes = [];
            public function send(Envelope $envelope): Envelope { $this->envelopes[] = $envelope; return $envelope; }
            public function get(): iterable { return $this->envelopes; }
            public function ack(Envelope $envelope): void {}
            public function reject(Envelope $envelope): void {}
        }
        $transport = new InMemoryTransport();
        $processor = new PaymentOutboxProcessor($em, $transport, new NullLogger());
        $n = $processor->process(10, false);
        $this->assertSame(1, $n);

        // 3) Consume
        $port = new NullOrderStatusPort(new NullLogger());
        $consumer = new PaymentEventConsumer(new class {
            public function onCaptured(string $p, int $a, string $c, ?string $g = null) {}
            public function onRefunded(string $p, int $a, string $c, ?string $g = null, ?string $r = null) {}
            public function onFailed(string $p, string $e, ?string $m = null) {}
        }, $port);

        foreach ($transport->envelopes as $env) {
            $msg = $env->getMessage();
            $consumer($msg);
        }

        $this->assertTrue(true); // smoke
    }
}
