<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stores the payment read-side projection snapshot.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_projection')]
class PaymentProjectionEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id = '';

    #[ORM\Column(name: 'order_id', type: 'string', length: 128, nullable: true)]
    private ?string $orderId = null;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    private string $amount = '0.00';

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = 'USD';

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = '';

    #[ORM\Column(name: 'provider_ref', type: 'string', length: 128, nullable: true)]
    private ?string $providerRef = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->updatedAt = new \DateTimeImmutable('now');
    }

    public function id(): string
    {
        return $this->id;
    }

    public function orderId(): ?string
    {
        return $this->orderId;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function providerRef(): ?string
    {
        return $this->providerRef;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function syncFrom(array $row): void
    {
        $this->orderId = '' !== trim((string) ($row['order_id'] ?? '')) ? (string) $row['order_id'] : null;
        $this->amount = (string) ($row['amount'] ?? '0.00');
        $this->currency = (string) ($row['currency'] ?? 'USD');
        $this->status = (string) ($row['status'] ?? '');
        $this->providerRef = isset($row['provider_ref']) && '' !== trim((string) $row['provider_ref']) ? (string) $row['provider_ref'] : null;
        $this->updatedAt = new \DateTimeImmutable((string) ($row['updated_at'] ?? 'now'));
    }
}
