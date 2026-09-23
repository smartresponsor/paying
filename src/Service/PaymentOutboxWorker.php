<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\Business\PaymentOutboxMessageEntity;
use App\Paying\Exception\PaymentOutboxOperationException;
use App\Paying\RepositoryInterface\PaymentOutboxRepositoryInterface;
use App\Paying\ServiceInterface\PaymentOutboxPublisherInterface;
use App\Paying\ServiceInterface\PaymentPublisherTransportInterface;
use Psr\Log\LoggerInterface;

/**
 * Processes pending payment outbox messages in worker-driven runtime flows.
 */
class PaymentOutboxWorker
{
    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly PaymentOutboxRepositoryInterface $outbox,
        private readonly PaymentPublisherTransportInterface $transport,
        private readonly PaymentOutboxPublisherInterface $outboxPublisher,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Runs one outbox publish pass and returns the number of successfully published items.
     */
    public function run(int $limit = 100, bool $retryFailed = false): int
    {
        $rows = $this->loadRows($limit, $retryFailed);
        $count = 0;

        foreach ($rows as $row) {
            $payload = $row->payload();
            $attempts = $row->attempts() + 1;
            $routingKey = null !== $row->routingKey() && '' !== $row->routingKey() ? $row->routingKey() : $row->type();
            $id = (string) $row->id();

            try {
                $this->transport->publish($routingKey, $payload);
                $row->incrementAttempts();
                $row->markPublished();
                $this->outbox->flush();
                ++$count;
            } catch (\Throwable $exception) {
                $reason = 'publish-failed: '.$exception->getMessage();
                $this->logger->error('Failed to publish outbox message.', [
                    'id' => $id,
                    'routingKey' => $routingKey,
                    'attempts' => $attempts,
                    'exception' => $exception,
                ]);

                if ($attempts >= self::MAX_ATTEMPTS) {
                    $this->outboxPublisher->moveToDlq($id, $reason);
                    continue;
                }

                try {
                    $row->incrementAttempts();
                    $row->markFailed($reason);
                    $this->outbox->flush();
                } catch (\Throwable $e) {
                    $this->logger->error('Failed to persist outbox failure status.', [
                        'id' => $id,
                        'reason' => $reason,
                        'exception' => $e,
                    ]);

                    throw new PaymentOutboxOperationException('Unable to persist outbox failure state.', 0, $e);
                }
            }
        }

        return $count;
    }

    /**
     * @return list<PaymentOutboxMessageEntity>
     */
    private function loadRows(int $limit, bool $retryFailed): array
    {
        try {
            $rows = $this->outbox->listProcessable($limit, $retryFailed);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to load outbox messages.', [
                'limit' => $limit,
                'retryFailed' => $retryFailed,
                'exception' => $e,
            ]);

            throw new PaymentOutboxOperationException('Unable to load outbox messages.', 0, $e);
        }

        return array_values(array_filter($rows, static fn (mixed $row): bool => $row instanceof PaymentOutboxMessageEntity));
    }
}
