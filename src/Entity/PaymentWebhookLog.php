<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

/**
 * Stores webhook deliveries received from upstream payment providers.
 *
 * The log supports idempotency checks, duplicate detection, and processing auditability for the
 * inbound webhook surface.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_webhook_log')]
#[ORM\UniqueConstraint(name: 'uniq_payment_webhook_provider_event', columns: ['provider', 'external_event_id'])]
class PaymentWebhookLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 32)]
    private string $provider;

    #[ORM\Column(name: 'external_event_id', type: 'string', length: 191)]
    private string $externalEventId;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'string', length: 16)]
    private string $status = 'received';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $duplicateCount = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $receivedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    public function __construct(string $provider, string $externalEventId, array $payload)
    {
        $this->id = (new Ulid())->toRfc4122();
        $this->provider = strtolower($provider);
        $this->externalEventId = $externalEventId;
        $this->payload = $payload;
        $this->receivedAt = new \DateTimeImmutable('now');
    }

    /**
     * Returns the stable webhook-log identifier.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the normalized upstream provider code.
     */
    public function provider(): string
    {
        return $this->provider;
    }

    /**
     * Returns the provider-side event identifier used for idempotency.
     */
    public function externalEventId(): string
    {
        return $this->externalEventId;
    }

    /**
     * Returns the raw webhook payload captured for processing and auditability.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * Returns the current processing status of the webhook delivery.
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * Returns how many duplicate deliveries were observed for the same provider event.
     */
    public function duplicateCount(): int
    {
        return $this->duplicateCount;
    }

    /**
     * Returns when the webhook delivery was first recorded.
     */
    public function receivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }

    /**
     * Returns when the webhook was marked as processed, if processing finished.
     */
    public function processedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    /**
     * Marks the webhook record as a duplicate delivery and increments the duplicate counter.
     */
    public function markDuplicate(): void
    {
        $this->status = 'duplicate';
        ++$this->duplicateCount;
    }

    /**
     * Marks the webhook delivery as processed and captures the completion timestamp.
     */
    public function markProcessed(): void
    {
        $this->status = 'processed';
        $this->processedAt = new \DateTimeImmutable('now');
    }
}
