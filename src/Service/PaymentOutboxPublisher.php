<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Exception\PaymentOutboxOperationException;
use App\Paying\RepositoryInterface\PaymentOutboxRepositoryInterface;
use App\Paying\ServiceInterface\PaymentOutboxPublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes queued payment outbox messages to the configured transport boundary.
 */
readonly class PaymentOutboxPublisher implements PaymentOutboxPublisherInterface
{
    public function __construct(
        private PaymentOutboxRepositoryInterface $outbox,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Queues a transport message for asynchronous publication.
     */
    public function enqueue(string $topic, array $payload): void
    {
        try {
            $this->outbox->enqueue($topic, $payload);
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
            if (!$this->outbox->moveToDlq($id, $reason)) {
                $this->logger->warning('Outbox message not found for DLQ move.', ['id' => $id, 'reason' => $reason]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to move outbox message to DLQ.', ['id' => $id, 'reason' => $reason, 'exception' => $e]);

            throw new PaymentOutboxOperationException('Unable to move outbox message to DLQ.', 0, $e);
        }
    }
}
