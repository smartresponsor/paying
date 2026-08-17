<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\Entity\PaymentDlqEntity;
use App\Paying\Entity\PaymentOutboxMessageEntity;
use App\Paying\Exception\PaymentOutboxOperationException;
use App\Paying\InfrastructureInterface\PaymentOutboxPublisherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Publishes queued payment outbox messages to the configured transport boundary.
 */
readonly class PaymentOutboxPublisher implements PaymentOutboxPublisherInterface
{
    public function __construct(
        private EntityManagerInterface $data,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Queues a transport message for asynchronous publication.
     */
    public function enqueue(string $topic, array $payload): void
    {
        try {
            $this->data->wrapInTransaction(function () use ($topic, $payload): void {
                $this->data->persist(new PaymentOutboxMessageEntity((new Ulid())->toRfc4122(), $topic, $payload, $topic));
                $this->data->flush();
            });
        } catch (\Throwable $e) {
            $this->logger->error('Failed to enqueue payment outbox message.', [
                'topic' => $topic,
                'payload' => $payload,
                'exception' => $e,
            ]);

            throw new PaymentOutboxOperationException('Unable to enqueue outbox message.', 0, $e);
        }
    }

    /**
     * Moves a failed transport message into the dead-letter queue.
     */
    public function moveToDlq(string $id, string $reason): void
    {
        try {
            $entity = $this->data->find(PaymentOutboxMessageEntity::class, $id);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to load outbox message for DLQ move.', ['id' => $id, 'exception' => $e]);

            throw new PaymentOutboxOperationException('Unable to read outbox message for DLQ move.', 0, $e);
        }

        if (!$entity instanceof PaymentOutboxMessageEntity) {
            $this->logger->warning('Outbox message not found for DLQ move.', ['id' => $id, 'reason' => $reason]);

            return;
        }

        try {
            $this->data->wrapInTransaction(function () use ($entity, $reason): void {
                $this->data->persist(new PaymentDlqEntity(
                    $entity->id(),
                    $entity->routingKey() ?? $entity->type(),
                    $entity->payload(),
                    $reason,
                ));
                $this->data->remove($entity);
                $this->data->flush();
            });
        } catch (\Throwable $e) {
            $this->logger->error('Failed to insert payment DLQ message.', ['id' => $id, 'reason' => $reason, 'exception' => $e]);

            throw new PaymentOutboxOperationException('Unable to insert DLQ message.', 0, $e);
        }
    }
}
