<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\PaymentStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

/**
 * Doctrine aggregate root that stores the canonical state of a payment lifecycle.
 *
 * The entity is intentionally small and mutation-oriented: state transitions are
 * performed through explicit helper methods such as {@see markProcessing()} or
 * {@see markRefunded()} so that callers do not update status and provider
 * references ad hoc.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment')]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(type: 'string', length: 128)]
    private string $orderId;

    #[ORM\Column(type: 'string', length: 16, enumType: PaymentStatus::class)]
    private PaymentStatus $status;

    /**
     * Decimal amount in major units, for example `50.00`.
     */
    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    /**
     * Provider-side identifier returned by the active payment provider.
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $providerRef = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param string $amount Decimal amount in major units.
     * @param string $currency ISO-4217 currency code.
     * @param string $orderId External order identifier. Falls back to payment ID when omitted.
     */
    public function __construct(Ulid $id, PaymentStatus $status, string $amount, string $currency, string $orderId = '')
    {
        $this->id = $id;
        $this->orderId = '' !== trim($orderId) ? trim($orderId) : (string) $id;
        $this->status = $status;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    /**
     * Refreshes the mutation timestamp before Doctrine updates the row.
     */
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Returns the immutable payment identifier.
     */
    public function id(): Ulid
    {
        return $this->id;
    }

    /**
     * Returns the external order identifier associated with the payment.
     */
    public function orderId(): string
    {
        return $this->orderId;
    }

    /**
     * Returns the current payment lifecycle status.
     */
    public function status(): PaymentStatus
    {
        return $this->status;
    }

    /**
     * Returns the decimal amount in major units.
     */
    public function amount(): string
    {
        return $this->amount;
    }

    /**
     * Returns the ISO-4217 currency code.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Returns the provider-side reference when one is known.
     */
    public function providerRef(): ?string
    {
        return $this->providerRef;
    }

    /**
     * Returns the creation timestamp.
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Returns the last mutation timestamp.
     */
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Updates the payment status and refreshes the mutation timestamp.
     */
    public function withStatus(PaymentStatus $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * Sets or clears the provider reference and refreshes the mutation timestamp.
     */
    public function withProviderRef(?string $ref): self
    {
        $this->providerRef = $ref;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * Marks the payment as processing.
     *
     * When a provider reference is supplied it becomes the canonical stored
     * reference for subsequent finalize, refund, or reconciliation flows.
     */
    public function markProcessing(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::processing);
    }

    /**
     * Marks the payment as completed.
     */
    public function markCompleted(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::completed);
    }

    /**
     * Marks the payment as failed.
     */
    public function markFailed(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::failed);
    }

    /**
     * Marks the payment as refunded.
     */
    public function markRefunded(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::refunded);
    }

    /**
     * Copies externally resolved state from another payment snapshot.
     *
     * This method is used when provider-facing logic returns a freshly composed
     * payment instance and the persisted aggregate needs to absorb the resolved
     * status, amount, currency, and provider reference.
     */
    public function syncFrom(self $payment): self
    {
        $this->amount = $payment->amount();
        $this->currency = $payment->currency();
        $this->providerRef = $payment->providerRef();

        return $this->withStatus($payment->status());
    }
}
