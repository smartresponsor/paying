<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\Operational\PaymentCircuitEntity;
use App\Paying\RepositoryInterface\PaymentOperationalRepositoryInterface;
use App\Paying\ServiceInterface\PaymentCircuitBreakerInterface;

/**
 * Provides the circuit breaker service used by the payment lifecycle and operator-facing flows.
 */
readonly class PaymentCircuitBreaker implements PaymentCircuitBreakerInterface
{
    public function __construct(
        private PaymentOperationalRepositoryInterface $operations,
        private int $threshold = 5,
        private int $cooldownSec = 60,
    ) {
    }

    /**
     * Determines whether the is open condition is currently satisfied.
     */
    public function isOpen(string $key): bool
    {
        $entity = $this->operations->findCircuit($key);
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
        $entity = $this->operations->findCircuit($key);
        if ($entity instanceof PaymentCircuitEntity) {
            $this->operations->removeCircuit($entity);
        }
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
        $entity = $this->operations->findCircuit($key);
        $count = $entity instanceof PaymentCircuitEntity ? $entity->failureCount() + 1 : 1;
        $retryAt = (new \DateTimeImmutable())->modify('+'.$this->cooldownSec.' seconds');

        if (!$entity instanceof PaymentCircuitEntity) {
            $entity = new PaymentCircuitEntity($key, $count, $retryAt);
        } else {
            $entity->recordFailure($count, $retryAt);
        }

        $this->operations->saveCircuit($entity);
    }
}
