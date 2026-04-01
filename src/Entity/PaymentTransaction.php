<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_transaction')]
class PaymentTransaction
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'guid')]
    private string $paymentId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $gatewayTransactionId;

    #[ORM\Column(type: 'string', length: 16)]
    private string $type;

    #[ORM\Column(type: 'integer')]
    private int $amountMinor;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $occurredAt;

    public function __construct(string $id, string $paymentId, string $gatewayTransactionId, string $type, int $amountMinor)
    {
        $this->id = $id;
        $this->paymentId = $paymentId;
        $this->gatewayTransactionId = $gatewayTransactionId;
        $this->type = $type;
        $this->amountMinor = $amountMinor;
        $this->occurredAt = new \DateTimeImmutable('now');
    }

    public function id(): string
    {
        return $this->id;
    }

    public function paymentId(): string
    {
        return $this->paymentId;
    }

    public function gatewayTransactionId(): string
    {
        return $this->gatewayTransactionId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function amountMinor(): int
    {
        return $this->amountMinor;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
