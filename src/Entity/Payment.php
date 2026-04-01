<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\PaymentStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'payment')]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

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

    public function __construct(Ulid $id, PaymentStatus $status, string $amount, string $currency)
    {
        $this->id = $id;
        $this->status = $status;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function providerRef(): ?string
    {
        return $this->providerRef;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return $this
     */
    /**
     * @return $this
     */
    public function withStatus(PaymentStatus $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return $this
     */
    /**
     * @return $this
     */
    public function withProviderRef(?string $ref): self
    {
        $this->providerRef = $ref;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return $this
     */
    /**
     * @return $this
     */
    public function markProcessing(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::processing);
    }

    /**
     * @return $this
     */
    /**
     * @return $this
     */
    public function markCompleted(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::completed);
    }

    /**
     * @return $this
     */
    /**
     * @return $this
     */
    public function markFailed(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::failed);
    }

    /**
     * @return $this
     */
    /**
     * @return $this
     */
    public function markRefunded(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->withStatus(PaymentStatus::refunded);
    }

    /**
     * @return $this
     */
    /**
     * @return $this
     */
    public function syncFrom(self $payment): self
    {
        $this->amount = $payment->amount();
        $this->currency = $payment->currency();
        $this->providerRef = $payment->providerRef();

        return $this->withStatus($payment->status());
    }
}
