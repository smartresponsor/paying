<?php

declare(strict_types=1);

namespace App\Paying\Entity;

use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectIdentityEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectStateEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Schedules a recurring payment attempt.
 *
 * Restored from the old PaymentRecurring entity. The subscription relation remains
 * a scalar subscription identifier to avoid hard coupling Paying to Subscripting.
 */
#[ORM\Entity(repositoryClass: \App\Paying\Repository\PaymentRecurringRepository::class)]
#[ORM\Table(name: 'payment_recurring')]
class PaymentRecurringEntity
{
    use ObjectIdentityEmbeddableTrait;
    use ObjectAuditEmbeddableTrait;
    use ObjectStateEmbeddableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $subscriptionId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $scheduledAt;

    #[ORM\Column(type: 'string', length: 16, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $transactionId = null;

    public function __construct(?string $subscriptionId = null, ?\DateTimeImmutable $scheduledAt = null)
    {
        $this->subscriptionId = $subscriptionId;
        $this->scheduledAt = $scheduledAt ?? new \DateTimeImmutable();
        $this->initializeObjectIdentity();
        $this->initializeObjectAudit();
        $this->initializeObjectState(objectStatus: $this->status);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function subscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId(?string $subscriptionId): void
    {
        $this->subscriptionId = $subscriptionId;
    }

    public function scheduledAt(): \DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeImmutable $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->setObjectStatus($status);
    }

    public function transactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }
}
