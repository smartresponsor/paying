<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Tracks circuit breaker state for operational payment workflows.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_circuit')]
class PaymentCircuitEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'guid', unique: true)]
    private string $slug;

    #[ORM\Column(name: 'key', type: 'string', length: 80, unique: true)]
    private string $key = '';

    #[ORM\Column(name: 'failure_count', type: 'integer')]
    private int $failureCount = 0;

    #[ORM\Column(name: 'retry_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $retryAt;

    public function __construct(string $key, int $failureCount, \DateTimeImmutable $retryAt)
    {
        $this->slug = Uuid::v7()->toRfc4122();
        $this->key = $key;
        $this->failureCount = $failureCount;
        $this->retryAt = $retryAt;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function failureCount(): int
    {
        return $this->failureCount;
    }

    public function retryAt(): \DateTimeImmutable
    {
        return $this->retryAt;
    }

    public function markSuccess(): void
    {
        $this->failureCount = 0;
    }

    public function recordFailure(int $failureCount, \DateTimeImmutable $retryAt): void
    {
        $this->failureCount = $failureCount;
        $this->retryAt = $retryAt;
    }
}
