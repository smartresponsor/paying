<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Exception\PaymentRepositoryReadException;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Persists and reloads payment records for the payment repository workflow boundary.
 */
final readonly class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Executes the save operation for the current payment workflow.
     */
    public function save(PaymentEntity $payment): void
    {
        $this->em->persist($payment);
        $this->em->flush();
    }

    /**
     * Looks up payment data through the find query path.
     */
    public function find(string $id): ?PaymentEntity
    {
        $payment = $this->em->find(PaymentEntity::class, $id);
        if (null === $payment) {
            return null;
        }

        if ($this->em->contains($payment)) {
            $this->em->refresh($payment);
        }

        return $payment;
    }

    /**
     * Looks up payment data through the find by order id query path.
     */
    public function findByOrderId(string $orderId): ?PaymentEntity
    {
        $payment = $this->em->getRepository(PaymentEntity::class)->findOneBy(['orderId' => $orderId]);
        if (!$payment instanceof PaymentEntity) {
            return null;
        }

        if ($this->em->contains($payment)) {
            $this->em->refresh($payment);
        }

        return $payment;
    }

    /**
     * Returns the collection assembled by the list recent query path.
     */
    public function listRecent(int $limit = 10): array
    {
        $limit = max(1, $limit);

        $payments = $this->em->getRepository(PaymentEntity::class)->findBy([], ['updatedAt' => 'DESC'], $limit);

        $recentPayments = [];
        foreach ($payments as $payment) {
            $recentPayments[] = $payment;
        }

        return $recentPayments;
    }

    /**
     * Returns the collection assembled by the list ids by statuses query path.
     */
    public function listIdsByStatuses(array $statuses, int $limit = 100): array
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $status): string => strtolower(trim((string) $status)),
            $statuses,
        ))));

        if ([] === $normalized || $limit < 1) {
            return [];
        }

        try {
            $rows = $this->em->createQueryBuilder()
                ->select('p.id')
                ->from(PaymentEntity::class, 'p')
                ->where('LOWER(p.status) IN (:statuses)')
                ->setParameter('statuses', $normalized)
                ->orderBy('p.updatedAt', 'ASC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getScalarResult();
        } catch (\Throwable $e) {
            $this->logger->error('Failed to list payment ids by statuses.', [
                'statuses' => $normalized,
                'limit' => $limit,
                'exception' => $e,
            ]);

            throw new PaymentRepositoryReadException('Unable to list payment ids by statuses.', 0, $e);
        }

        return array_values(array_map(
            static fn (array $row): string => (string) ($row['id'] ?? $row[0] ?? ''),
            $rows,
        ));
    }

    public function listUpdatedAfter(\DateTimeImmutable $updatedAfter, int $limit = 500): array
    {
        $limit = max(1, $limit);

        $payments = $this->em->createQueryBuilder()
            ->select('p')
            ->from(PaymentEntity::class, 'p')
            ->where('p.updatedAt > :updatedAfter')
            ->setParameter('updatedAfter', $updatedAfter)
            ->orderBy('p.updatedAt', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_values(array_filter($payments, static fn (mixed $payment): bool => $payment instanceof PaymentEntity));
    }

    public function listAllOrderedByUpdatedAt(int $limit = 1000, int $offset = 0): array
    {
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $payments = $this->em->createQueryBuilder()
            ->select('p')
            ->from(PaymentEntity::class, 'p')
            ->orderBy('p.updatedAt', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_values(array_filter($payments, static fn (mixed $payment): bool => $payment instanceof PaymentEntity));
    }

    public function maxUpdatedAt(): ?string
    {
        try {
            $value = $this->em->createQueryBuilder()
                ->select('MAX(p.updatedAt)')
                ->from(PaymentEntity::class, 'p')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read payment max updated_at.', ['exception' => $e]);

            throw new PaymentRepositoryReadException('Unable to read payment max updated_at.', 0, $e);
        }

        return is_string($value) && '' !== trim($value) ? $value : null;
    }

    public function countByStatusSince(\DateTimeImmutable $since): array
    {
        try {
            $rows = $this->em->createQueryBuilder()
                ->select('p.status AS status, COUNT(p.id) AS count')
                ->from(PaymentEntity::class, 'p')
                ->where('p.updatedAt >= :since')
                ->setParameter('since', $since)
                ->groupBy('p.status')
                ->getQuery()
                ->getScalarResult();
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read payment counts by status.', ['exception' => $e, 'since' => $since->format(DATE_ATOM)]);

            throw new PaymentRepositoryReadException('Unable to read payment counts by status.', 0, $e);
        }

        $counts = [];
        foreach ($rows as $row) {
            $status = strtolower((string) ($row['status'] ?? ''));
            if ('' === $status) {
                continue;
            }

            $counts[$status] = (int) ($row['count'] ?? 0);
        }

        return $counts;
    }
}
