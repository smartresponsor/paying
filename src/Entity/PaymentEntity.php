<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Entity;

use App\Paying\ValueObject\PaymentStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\AbstractUid;

/**
 * Stores the canonical payment aggregate snapshot used across the operational lifecycle.
 *
 * The entity tracks order identity, monetary data, provider correlation, and the current status
 * that downstream services rely on for orchestration and reporting.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment')]
#[ORM\HasLifecycleCallbacks]
class PaymentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'guid', unique: true)]
    private string $slug;

    #[ORM\Column(type: 'string', length: 128)]
    private string $orderId;

    #[ORM\Column(type: 'string', length: 16, enumType: PaymentStatus::class)]
    private PaymentStatus $status;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $providerRef = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(AbstractUid $slug, PaymentStatus $status, string $amount, string $currency, string $orderId = '')
    {
        $this->slug = $slug->toRfc4122();
        $this->orderId = '' !== trim($orderId) ? trim($orderId) : $this->slug;
        $this->status = $status;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    /**
     * Refreshes the update timestamp before Doctrine persists an in-place change.
     */
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Returns the stable payment identifier used across repository and transport boundaries.
     */
    public function id(): ?int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * Returns the upstream order identifier associated with the payment.
     */
    public function orderId(): string
    {
        return $this->orderId;
    }

    /**
     * Returns the current lifecycle status used by orchestration and reporting flows.
     */
    public function status(): PaymentStatus
    {
        return $this->status;
    }

    /**
     * Returns the normalized decimal amount captured for the payment.
     */
    public function amount(): string
    {
        return $this->amount;
    }

    /**
     * Returns the ISO currency code used for the payment amount.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Returns the external provider reference when the payment has been correlated upstream.
     */
    public function providerRef(): ?string
    {
        return $this->providerRef;
    }

    /**
     * Returns the creation timestamp for audit and ordering purposes.
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Returns the most recent mutation timestamp for the payment snapshot.
     */
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Applies a new lifecycle status and refreshes the modification timestamp.
     */
    public function withStatus(PaymentStatus $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * Applies a provider reference received from the upstream payment processor.
     */
    public function withProviderRef(?string $ref): self
    {
        $this->providerRef = $ref;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * Marks the payment as being processed and optionally attaches the provider reference.
     */
    public function markProcessing(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::processing);
    }

    /**
     * Marks the payment as completed and optionally stores the upstream provider reference.
     */
    public function markCompleted(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::completed);
    }

    /**
     * Marks the payment as failed and optionally stores the provider-side correlation reference.
     */
    public function markFailed(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::failed);
    }

    /**
     * Marks the payment as refunded and optionally stores the provider-side correlation reference.
     */
    public function markRefunded(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::refunded);
    }

    /**
     * Synchronizes the mutable payment snapshot from another instance of the same aggregate.
     */
    public function syncFrom(self $payment): self
    {
        $this->amount = $payment->amount();
        $this->currency = $payment->currency();
        $this->providerRef = $payment->providerRef();

        return $this->withStatus($payment->status());
    }
}
