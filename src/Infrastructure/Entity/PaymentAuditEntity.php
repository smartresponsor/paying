<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Persists payment audit records for operator and system-visible lifecycle actions.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_audit')]
class PaymentAuditEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null; // @phpstan-ignore-line Doctrine assigns the generated identifier at runtime.

    #[ORM\Column(type: 'string', length: 80)]
    private string $action;

    #[ORM\Column(type: 'json')]
    private array $context;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $action, array $context = [])
    {
        $this->action = $action;
        $this->context = $context;
        $this->createdAt = new \DateTimeImmutable('now');
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function action(): string
    {
        return $this->action;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
