<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\Business\PaymentDlqEntity;
use App\Paying\Entity\Business\PaymentOutboxMessageEntity;
use App\Paying\RepositoryInterface\PaymentOutboxRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Owns Doctrine access for the durable payment outbox and dead-letter queue.
 */
final readonly class PaymentOutboxRepository implements PaymentOutboxRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function listProcessable(int $limit, bool $retryFailed): array
    {
        $statuses = $retryFailed ? ['pending', 'failed'] : ['pending'];
        $rows = $this->em->createQueryBuilder()
            ->select('m')
            ->from(PaymentOutboxMessageEntity::class, 'm')
            ->where('m.status IN (:statuses)')
            ->setParameter('statuses', $statuses)
            ->orderBy('m.occurredAt', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();

        return array_values(array_filter($rows, static fn (mixed $row): bool => $row instanceof PaymentOutboxMessageEntity));
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    public function enqueue(string $topic, array $payload): void
    {
        $this->em->wrapInTransaction(function () use ($topic, $payload): void {
            $this->em->persist(new PaymentOutboxMessageEntity((new Ulid())->toRfc4122(), $topic, $payload, $topic));
            $this->em->flush();
        });
    }

    public function moveToDlq(string $id, string $reason): bool
    {
        $entity = $this->em->find(PaymentOutboxMessageEntity::class, $id);
        if (!$entity instanceof PaymentOutboxMessageEntity) {
            return false;
        }

        $this->em->wrapInTransaction(function () use ($entity, $reason): void {
            $this->em->persist(new PaymentDlqEntity(
                (string) $entity->id(),
                $entity->routingKey() ?? $entity->type(),
                $entity->payload(),
                $reason,
            ));
            $this->em->remove($entity);
            $this->em->flush();
        });

        return true;
    }

    public function listDlq(int $limit = 200): array
    {
        $rows = $this->em->createQueryBuilder()
            ->select('d')
            ->from(PaymentDlqEntity::class, 'd')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();

        return array_values(array_filter($rows, static fn (mixed $row): bool => $row instanceof PaymentDlqEntity));
    }

    public function replayDlq(int $id): bool
    {
        $entity = $this->em->find(PaymentDlqEntity::class, $id);
        if (!$entity instanceof PaymentDlqEntity) {
            return false;
        }

        $this->em->wrapInTransaction(function () use ($entity): void {
            $this->em->persist(new PaymentOutboxMessageEntity(
                (new Ulid())->toRfc4122(),
                $entity->topic(),
                $entity->payload(),
                $entity->topic(),
            ));
            $this->em->remove($entity);
            $this->em->flush();
        });

        return true;
    }
}
