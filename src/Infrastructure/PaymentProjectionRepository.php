<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\Infrastructure\Entity\PaymentProjectionEntity;
use App\Paying\Infrastructure\Entity\PaymentProjectionMetaEntity;
use App\Paying\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Persists and queries payment projection records for read-side use cases.
 */
readonly class PaymentProjectionRepository implements PaymentProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $infrastructure,
        private LoggerInterface $logger,
    ) {
    }

    public function findById(string $id): ?array
    {
        try {
            $entity = $this->infrastructure->find(PaymentProjectionEntity::class, $id);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to fetch payment projection by ID.', ['id' => $id, 'exception' => $e]);

            throw $e;
        }

        if (!$entity instanceof PaymentProjectionEntity) {
            return null;
        }

        return $this->toRow($entity);
    }

    public function listByStatus(string $status, int $limit = 100): array
    {
        try {
            $entities = $this->infrastructure->createQueryBuilder()
                ->select('p')
                ->from(PaymentProjectionEntity::class, 'p')
                ->where('p.status = :status')
                ->setParameter('status', $status)
                ->orderBy('p.updatedAt', 'DESC')
                ->setMaxResults(max(1, $limit))
                ->getQuery()
                ->getResult();
        } catch (\Throwable $e) {
            $this->logger->error('Failed to list payment projections by status.', ['status' => $status, 'limit' => $limit, 'exception' => $e]);

            throw $e;
        }

        return array_values(array_map(
            fn (PaymentProjectionEntity $entity): array => $this->toRow($entity),
            array_filter($entities, static fn (mixed $entity): bool => $entity instanceof PaymentProjectionEntity),
        ));
    }

    public function upsert(array $row): void
    {
        $id = (string) ($row['id'] ?? '');
        if ('' === $id) {
            throw new \InvalidArgumentException('Projection row id is required.');
        }

        $this->infrastructure->wrapInTransaction(function () use ($id, $row): void {
            $entity = $this->infrastructure->find(PaymentProjectionEntity::class, $id);
            if (!$entity instanceof PaymentProjectionEntity) {
                $entity = new PaymentProjectionEntity($id);
                $this->infrastructure->persist($entity);
            }

            $entity->syncFrom([
                'order_id' => $row['order_id'] ?? $row['orderId'] ?? null,
                'amount' => $row['amount'] ?? '0.00',
                'currency' => $row['currency'] ?? 'USD',
                'status' => $row['status'] ?? '',
                'provider_ref' => $row['provider_ref'] ?? ($row['providerRef'] ?? null),
                'updated_at' => $row['updated_at'] ?? $row['updatedAt'] ?? gmdate(DATE_ATOM),
            ]);

            $this->infrastructure->flush();
        });
    }

    public function maxUpdatedAt(): ?string
    {
        try {
            $value = $this->infrastructure->createQueryBuilder()
                ->select('MAX(p.updatedAt)')
                ->from(PaymentProjectionEntity::class, 'p')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read payment projection max updated_at.', ['exception' => $e]);

            throw $e;
        }

        return is_string($value) && '' !== trim($value) ? $value : null;
    }

    public function watermark(): ?string
    {
        try {
            $entity = $this->infrastructure->find(PaymentProjectionMetaEntity::class, 'watermark');
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read payment projection watermark.', ['exception' => $e]);

            throw $e;
        }

        if (!$entity instanceof PaymentProjectionMetaEntity) {
            return null;
        }

        return $entity->value();
    }

    public function saveWatermark(string $ts): void
    {
        $this->infrastructure->wrapInTransaction(function () use ($ts): void {
            $entity = $this->infrastructure->find(PaymentProjectionMetaEntity::class, 'watermark');
            if (!$entity instanceof PaymentProjectionMetaEntity) {
                $entity = new PaymentProjectionMetaEntity('watermark', $ts);
                $this->infrastructure->persist($entity);
            } else {
                $entity->setValue($ts);
            }

            $this->infrastructure->flush();
        });
    }

    private function toRow(PaymentProjectionEntity $entity): array
    {
        return [
            'id' => $entity->id(),
            'order_id' => $entity->orderId(),
            'amount' => $entity->amount(),
            'currency' => $entity->currency(),
            'status' => $entity->status(),
            'provider_ref' => $entity->providerRef(),
            'updated_at' => $entity->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
