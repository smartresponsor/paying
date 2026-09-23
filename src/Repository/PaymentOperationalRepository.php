<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\Operational\PaymentAuditEntity;
use App\Paying\Entity\Operational\PaymentCircuitEntity;
use App\Paying\Entity\Operational\PaymentIdempotencyEntity;
use App\Paying\RepositoryInterface\PaymentOperationalRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Owns Doctrine access for Paying's operational persistence concerns.
 */
final readonly class PaymentOperationalRepository implements PaymentOperationalRepositoryInterface
{
    public function __construct(private EntityManagerInterface $infrastructure)
    {
    }

    public function idempotencyGet(string $key): ?string
    {
        $entity = $this->infrastructure->find(PaymentIdempotencyEntity::class, $key);
        if (!$entity instanceof PaymentIdempotencyEntity) {
            return null;
        }

        if ($entity->expiresAt()->getTimestamp() < time()) {
            $this->infrastructure->wrapInTransaction(function () use ($entity): void {
                $this->infrastructure->remove($entity);
                $this->infrastructure->flush();
            });

            return null;
        }

        return $entity->value();
    }

    public function idempotencyPut(string $key, string $value, int $ttlSec): void
    {
        $expiresAt = new \DateTimeImmutable("+{$ttlSec} seconds");
        $this->infrastructure->wrapInTransaction(function () use ($key, $value, $expiresAt): void {
            $entity = $this->infrastructure->find(PaymentIdempotencyEntity::class, $key);
            if (!$entity instanceof PaymentIdempotencyEntity) {
                $entity = new PaymentIdempotencyEntity($key, $value, $expiresAt);
                $this->infrastructure->persist($entity);
            } else {
                $entity->refresh($value, $expiresAt);
            }

            $this->infrastructure->flush();
        });
    }

    public function purgeExpiredIdempotency(): int
    {
        return (int) $this->infrastructure->createQueryBuilder()
            ->delete(PaymentIdempotencyEntity::class, 'i')
            ->where('i.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable('now'))
            ->getQuery()
            ->execute();
    }

    public function writeAudit(string $action, array $context = []): void
    {
        $this->infrastructure->wrapInTransaction(function () use ($action, $context): void {
            $this->infrastructure->persist(new PaymentAuditEntity($action, $context));
            $this->infrastructure->flush();
        });
    }

    public function findCircuit(string $key): ?PaymentCircuitEntity
    {
        $entity = $this->infrastructure->getRepository(PaymentCircuitEntity::class)->findOneBy(['key' => $key]);

        return $entity instanceof PaymentCircuitEntity ? $entity : null;
    }

    public function saveCircuit(PaymentCircuitEntity $entity): void
    {
        $this->infrastructure->wrapInTransaction(function () use ($entity): void {
            $this->infrastructure->persist($entity);
            $this->infrastructure->flush();
        });
    }

    public function removeCircuit(PaymentCircuitEntity $entity): void
    {
        $this->infrastructure->wrapInTransaction(function () use ($entity): void {
            $this->infrastructure->remove($entity);
            $this->infrastructure->flush();
        });
    }
}
