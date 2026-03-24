<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_refund')]
class PaymentRefund
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'guid')]
    private string $paymentId;

    #[ORM\Column(type: 'integer')]
    private int $amountMinor;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $refundedAt;

    public function __construct(string $id, string $paymentId, int $amountMinor, string $currency, ?string $reason = null)
    {
        $this->id = $id;
        $this->paymentId = $paymentId;
        $this->amountMinor = $amountMinor;
        $this->currency = $currency;
        $this->reason = $reason;
        $this->refundedAt = new \DateTimeImmutable('now');
    }
}
