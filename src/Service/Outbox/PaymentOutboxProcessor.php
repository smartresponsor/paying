<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Outbox;

use App\Paying\Entity\PaymentOutboxMessage;
use App\Paying\Message\Event\PaymentTransportMessage;
use App\Paying\ServiceInterface\Outbox\PaymentOutboxProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Provides the payment outbox processor service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentOutboxProcessor implements PaymentOutboxProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private TransportInterface $transport,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Provides the process behavior for the payment outbox processor component.
     */
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
