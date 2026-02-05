<?php
namespace OrderComponent\Payment\Service\Payment\Outbox;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Entity\Payment\PaymentOutboxMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class PaymentOutboxProcessor
{
    public function __construct(
        private EntityManagerInterface $em,
        private TransportInterface $transport, // @messenger.transport.payment_outbox
        private LoggerInterface $logger
    ) {}

    public function process(int $limit = 50, bool $retryFailed = false): int
    {
        $repo = $this->em->getRepository(PaymentOutboxMessage::class);
        $qb = $repo->createQueryBuilder('o')->where('o.status = :pending')->setParameter('pending', 'pending');
        if ($retryFailed) {
            $qb->orWhere('o.status = :failed')->setParameter('failed', 'failed');
        }
        $messages = $qb->setMaxResults($limit)->getQuery()->getResult();
        $count = 0;
        foreach ($messages as $m) {
            if (!$m instanceof PaymentOutboxMessage) { continue; }
            try {
                $envelope = new Envelope((object)['type' => $m->type(), 'payload' => $m->payload()], [new TransportNamesStamp(['payment_outbox'])]);
                $this->transport->send($envelope);
                $m->markPublished();
                $this->logger->info('Payment outbox published', ['id' => $m->id(), 'type' => $m->type()]);
                $count++;
            } catch (\Throwable $e) {
                $m->incrementAttempts();
                $m->markFailed($e->getMessage());
                $this->logger->error('Payment outbox failed', ['id' => $m->id(), 'err' => $e->getMessage()]);
            }
        }
        $this->em->flush();
        return $count;
    }
}
