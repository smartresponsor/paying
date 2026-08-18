<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Infrastructure\Entity\PaymentCircuitEntity;
use App\Paying\ServiceInterface\PaymentCircuitBreakerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Provides the circuit breaker service used by the payment lifecycle and operator-facing flows.
 */
readonly class PaymentCircuitBreaker implements PaymentCircuitBreakerInterface
{
    public function __construct(
        private EntityManagerInterface $infrastructure,
        private int $threshold = 5,
        private int $cooldownSec = 60,
    ) {
    }

    /**
     * Determines whether the is open condition is currently satisfied.
     */
    public function isOpen(string $key): bool
    {
        $entity = $this->findCircuit($key);
        if (!$entity instanceof PaymentCircuitEntity) {
            return false;
        }

        return $entity->failureCount() >= $this->threshold && time() < $entity->retryAt()->getTimestamp();
    }

    /**
     * Records the state transition performed by the record success operation.
     */
    public function recordSuccess(string $key): void
    {
        $this->infrastructure->wrapInTransaction(function () use ($key): void {
            $entity = $this->findCircuit($key);
            if ($entity instanceof PaymentCircuitEntity) {
                $this->infrastructure->remove($entity);
            }

            $this->infrastructure->flush();
        });
    }

    /**
     * Records the state transition performed by the record failure operation.
     *
     * @param string $key
     *
     * @throws \DateMalformedStringException
     */
    public function recordFailure(string $key): void
    {
        $this->infrastructure->wrapInTransaction(function () use ($key): void {
            $entity = $this->findCircuit($key);
            $count = $entity instanceof PaymentCircuitEntity ? $entity->failureCount() + 1 : 1;
            $retryAt = (new \DateTimeImmutable())->modify('+'.$this->cooldownSec.' seconds');

            if (!$entity instanceof PaymentCircuitEntity) {
                $entity = new PaymentCircuitEntity($key, $count, $retryAt);
                $this->infrastructure->persist($entity);
            } else {
                $entity->recordFailure($count, $retryAt);
            }

            $this->infrastructure->flush();
        });
    }

    private function findCircuit(string $key): ?PaymentCircuitEntity
    {
        $entity = $this->infrastructure->getRepository(PaymentCircuitEntity::class)->findOneBy([
            'key' => $key,
        ]);

        return $entity instanceof PaymentCircuitEntity ? $entity : null;
    }
}
