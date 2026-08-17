<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stores a payment-domain message that still needs to be published to external transport.
 *
 * The entity carries the serialized payload, routing metadata, retry counters, and failure state
 * required by the outbox delivery workflow.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_outbox_message')]
class PaymentOutboxMessageEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 128)]
    private string $type;

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
    private ?string $routingKey;

    public function __construct(string $id, string $type, array $payload, ?string $routingKey = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->payload = $payload;
        $this->occurredAt = new \DateTimeImmutable('now');
        $this->routingKey = $routingKey;
    }

    /**
     * Returns the stable identifier of the persisted outbox record.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the logical message type used by downstream consumers.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Returns the serialized message payload scheduled for publication.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * Returns the transport routing key when the message targets a specific route.
     */
    public function routingKey(): ?string
    {
        return $this->routingKey;
    }

    /**
     * Marks the message as successfully published.
     */
    public function markPublished(): void
    {
        $this->status = 'published';
        $this->lastError = null;
    }

    /**
     * Marks the message as failed and stores the last transport-level error.
     */
    public function markFailed(string $error): void
    {
        $this->status = 'failed';
        $this->lastError = $error;
    }

    /**
     * Increments the delivery-attempt counter used by retry logic.
     */
    public function incrementAttempts(): void
    {
        ++$this->attempts;
    }

    /**
     * Returns the current outbox delivery status.
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * Returns how many publication attempts were already made for this message.
     */
    public function attempts(): int
    {
        return $this->attempts;
    }

    /**
     * Returns when the domain event was persisted into the outbox.
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Returns the latest transport error recorded for the outbox entry.
     */
    public function lastError(): ?string
    {
        return $this->lastError;
    }
}
