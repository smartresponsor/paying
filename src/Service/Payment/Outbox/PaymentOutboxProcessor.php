<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment\Outbox;
use App\ServiceInterface\Payment\Outbox\PaymentOutboxProcessorInterface;
use App\Entity\Payment\PaymentOutboxMessage;
use App\Message\Event\Payment\PaymentTransportMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class PaymentOutboxProcessor implements PaymentOutboxProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransportInterface $transport,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(int $limit = 50, bool $retryFailed = false): int
    {
        $repo = $this->em->getRepository(PaymentOutboxMessage::class);
        $qb = $repo->createQueryBuilder('o')
            ->where('o.status = :pending')
            ->setParameter('pending', 'pending');

        if ($retryFailed) {
            $qb->orWhere('o.status = :failed')
                ->setParameter('failed', 'failed');
        }

        $messages = $qb->setMaxResults($limit)->getQuery()->getResult();
        $count = 0;

        foreach ($messages as $message) {
            if (!$message instanceof PaymentOutboxMessage) {
                continue;
            }

            $message->incrementAttempts();

            try {
                $transportMessage = new PaymentTransportMessage($message->type(), $message->payload());
                $envelope = new Envelope($transportMessage, [new TransportNamesStamp(['payment_outbox'])]);
                $this->transport->send($envelope);
                $message->markPublished();
                $this->logger->info('Payment outbox published', [
                    'id' => $message->id(),
                    'type' => $message->type(),
                    'attempts' => $message->attempts(),
                ]);
                ++$count;
            } catch (\Throwable $exception) {
                $message->markFailed($exception->getMessage());
                $this->logger->error('Payment outbox failed', [
                    'id' => $message->id(),
                    'type' => $message->type(),
                    'attempts' => $message->attempts(),
                    'err' => $exception->getMessage(),
                ]);
            }
        }

        $this->em->flush();

        return $count;
    }
}
