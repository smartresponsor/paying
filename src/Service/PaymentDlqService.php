<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\Business\PaymentDlqEntity;
use App\Paying\RepositoryInterface\PaymentOutboxRepositoryInterface;
use App\Paying\ServiceInterface\PaymentDlqServiceInterface;

/**
 * Provides the dlq service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentDlqService implements PaymentDlqServiceInterface
{
    public function __construct(private PaymentOutboxRepositoryInterface $outbox)
    {
    }

    /**
     * Returns the collection assembled by the list query path.
     *
     * @return list<array{id: int, outbox_id: string, topic: string, reason: string, created_at: string}>
     */
    public function list(): array
    {
        $rows = $this->outbox->listDlq(200);

        return array_map(
            static fn (PaymentDlqEntity $row): array => [
                'id' => (int) ($row->id() ?? 0),
                'outbox_id' => $row->outboxId(),
                'topic' => $row->topic(),
                'reason' => $row->reason(),
                'created_at' => $row->createdAt()->format(DATE_ATOM),
            ],
            array_values(array_filter($rows, static fn (mixed $row): bool => $row instanceof PaymentDlqEntity)),
        );
    }

    /**
     * Executes the replay operation for the current payment workflow.
     */
    public function replay(int $id): bool
    {
        return $this->outbox->replayDlq($id);
    }
}
