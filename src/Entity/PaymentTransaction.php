<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stores a gateway-side transaction that belongs to a payment lifecycle.
 */
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

    /**
     * Returns the stable transaction identifier.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the identifier of the payment this transaction belongs to.
     */
    public function paymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * Returns the upstream gateway transaction identifier.
     */
    public function gatewayTransactionId(): string
    {
        return $this->gatewayTransactionId;
    }

    /**
     * Returns the transaction type recorded for the payment event.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Returns the transaction amount in minor units.
     */
    public function amountMinor(): int
    {
        return $this->amountMinor;
    }

    /**
     * Returns when the transaction occurred according to the component record.
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
