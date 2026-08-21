<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stores idempotency keys for payment operational flows.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_idempotency')]
class PaymentIdempotencyEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'key', type: 'string', length: 80)]
    private string $key = '';

    #[ORM\Column(type: 'text')]
    private string $value = '';

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    public function __construct(string $key, string $value, \DateTimeImmutable $expiresAt)
    {
        $this->key = $key;
        $this->value = $value;
        $this->expiresAt = $expiresAt;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function refresh(string $value, \DateTimeImmutable $expiresAt): void
    {
        $this->value = $value;
        $this->expiresAt = $expiresAt;
    }
}
