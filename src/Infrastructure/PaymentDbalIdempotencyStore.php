<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\Infrastructure\Entity\PaymentIdempotencyEntity;
use App\Paying\ServiceInterface\IdempotencyStoreInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Stores payment idempotency keys in the relational operational database.
 */
readonly class PaymentDbalIdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(private EntityManagerInterface $data)
    {
    }

    /**
     * Loads a stored idempotency value when the key is present and not expired.
     */
    public function get(string $key): ?string
    {
        $entity = $this->data->find(PaymentIdempotencyEntity::class, $key);
        if (!$entity instanceof PaymentIdempotencyEntity) {
            return null;
        }

        if ($entity->expiresAt()->getTimestamp() < time()) {
            $this->data->wrapInTransaction(function () use ($entity): void {
                $this->data->remove($entity);
                $this->data->flush();
            });

            return null;
        }

        return $entity->value();
    }

    /**
     * Stores or refreshes an idempotency value with a new expiration window.
     */
    public function put(string $key, string $value, int $ttlSec): void
    {
        $expiresAt = new \DateTimeImmutable("+{$ttlSec} seconds");

        $this->data->wrapInTransaction(function () use ($key, $value, $expiresAt): void {
            $entity = $this->data->find(PaymentIdempotencyEntity::class, $key);
            if (!$entity instanceof PaymentIdempotencyEntity) {
                $entity = new PaymentIdempotencyEntity($key, $value, $expiresAt);
                $this->data->persist($entity);
            } else {
                $entity->refresh($value, $expiresAt);
            }

            $this->data->flush();
        });
    }

    /**
     * Removes expired idempotency records and returns the affected row count.
     */
    public function purgeExpired(): int
    {
        return (int) $this->data->createQueryBuilder()
            ->delete(PaymentIdempotencyEntity::class, 'i')
            ->where('i.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable('now'))
            ->getQuery()
            ->execute();
    }
}
