<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_outbox_message')]
class PaymentOutboxMessage
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 128)]
    private string $type;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $occurredAt;

    #[ORM\Column(type: 'string', length: 32, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $attempts = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $routingKey = null;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(string $id, string $type, array $payload, ?string $routingKey = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->payload = $payload;
        $this->occurredAt = new \DateTimeImmutable('now');
        $this->routingKey = $routingKey;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->payload;
    }

    public function routingKey(): ?string
    {
        return $this->routingKey;
    }

    public function markPublished(): void
    {
        $this->status = 'published';
    }

    public function markFailed(string $error): void
    {
        $this->status = 'failed';
        $this->lastError = $error;
    }

    public function incrementAttempts(): void
    {
        ++$this->attempts;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }
}
