<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentDlqEntity;
use App\Paying\Entity\PaymentOutboxMessageEntity;
use App\Paying\ServiceInterface\PaymentDlqServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the dlq service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentDlqService implements PaymentDlqServiceInterface
{
    public function __construct(private EntityManagerInterface $data)
    {
    }

    /**
     * Returns the collection assembled by the list query path.
     *
     * @return list<array{id: int, outbox_id: string, topic: string, reason: string, created_at: string}>
     */
    public function list(): array
    {
        $rows = $this->data->createQueryBuilder()
            ->select('d')
            ->from(PaymentDlqEntity::class, 'd')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();

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
        $entity = $this->data->find(PaymentDlqEntity::class, $id);
        if (!$entity instanceof PaymentDlqEntity) {
            return false;
        }

        $this->data->wrapInTransaction(function () use ($entity): void {
            $this->data->persist(new PaymentOutboxMessageEntity(
                (new Ulid())->toRfc4122(),
                $entity->topic(),
                $entity->payload(),
                $entity->topic(),
            ));
            $this->data->remove($entity);
            $this->data->flush();
        });

        return true;
    }
}
