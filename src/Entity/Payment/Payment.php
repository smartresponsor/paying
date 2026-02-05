<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Entity\Payment;

use App\Domain\Payment\PaymentStatus;
use App\Infrastructure\Payment\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: "payment")]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: "ulid", unique: true)]
    private Ulid $id;

    #[ORM\Column(type: "string", length: 16, enumType: PaymentStatus::class)]
    private PaymentStatus $status;

    #[ORM\Column(type: "decimal", precision: 14, scale: 2)]
    private string $amount;

    #[ORM\Column(type: "string", length: 3)]
    private string $currency;

    #[ORM\Column(type: "string", length: 128, nullable: true)]
    private ?string $providerRef = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
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

    public function id(): Ulid { return $this->id; }
    public function status(): PaymentStatus { return $this->status; }
    public function amount(): string { return $this->amount; }
    public function currency(): string { return $this->currency; }
    public function providerRef(): ?string { return $this->providerRef; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function withStatus(PaymentStatus $status): self { $this->status = $status; return $this; }
    public function withProviderRef(?string $ref): self { $this->providerRef = $ref; return $this; }
}
