<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stores a dead-letter queue record for payment outbox replays.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_dlq')]
class PaymentDlqEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null; // @phpstan-ignore-line Doctrine assigns the generated identifier at runtime.

    #[ORM\Column(name: 'outbox_id', type: 'guid')]
    private string $outboxId;

    #[ORM\Column(type: 'string', length: 128)]
    private string $topic;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'text')]
    private string $reason;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $outboxId, string $topic, array $payload, string $reason)
    {
        $this->outboxId = $outboxId;
        $this->topic = $topic;
        $this->payload = $payload;
        $this->reason = $reason;
        $this->createdAt = new \DateTimeImmutable('now');
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function outboxId(): string
    {
        return $this->outboxId;
    }

    public function topic(): string
    {
        return $this->topic;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
