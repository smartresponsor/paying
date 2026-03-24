<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Entity\Payment;

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
    private string $type; // 'authorize'|'capture'|'refund'

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
}
