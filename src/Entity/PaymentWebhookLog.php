<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

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

    public function id(): string
    {
        return $this->id;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function externalEventId(): string
    {
        return $this->externalEventId;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->payload;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function duplicateCount(): int
    {
        return $this->duplicateCount;
    }

    public function receivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function processedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function markDuplicate(): void
    {
        $this->status = 'duplicate';
        ++$this->duplicateCount;
    }

    public function markProcessed(): void
    {
        $this->status = 'processed';
        $this->processedAt = new \DateTimeImmutable('now');
    }
}
