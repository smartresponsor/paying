<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use App\Service\PaymentStatusTransitionPolicy;
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

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function orderId(): string
    {
        return $this->orderId;
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

    public function transitionTo(PaymentStatus $status): self
    {
        PaymentStatusTransitionPolicy::assertCanTransition($this->status, $status);
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function withStatus(PaymentStatus $status): self
    {
        return $this->transitionTo($status);
    }

    public function withProviderRef(?string $ref): self
    {
        $this->providerRef = $ref;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markProcessing(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->transitionTo(PaymentStatus::processing);
    }

    public function markCompleted(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->transitionTo(PaymentStatus::completed);
    }

    public function markFailed(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->transitionTo(PaymentStatus::failed);
    }

    public function markRefunded(?string $providerRef = null): self
    {
        if (null !== $providerRef) {
            $this->providerRef = $providerRef;
        }

        return $this->transitionTo(PaymentStatus::refunded);
    }

    public function syncFrom(self $payment): self
    {
        $this->amount = $payment->amount();
        $this->currency = $payment->currency();
        $this->providerRef = $payment->providerRef();

        return $this->transitionTo($payment->status());
    }
}
