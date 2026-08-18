<?php

declare(strict_types=1);

namespace App\Paying\Entity;

use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectIdentityEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectSoftDeleteEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Stores a reusable/temporary token bound to a payment method.
 *
 * Restored from the old PaymentToken entity; system identity/audit/delete fields
 * are provided by Objecting embeddables instead of local duplicated traits.
 */
#[ORM\Entity(repositoryClass: \App\Paying\Repository\PaymentTokenRepository::class)]
#[ORM\Table(name: 'payment_token')]
class PaymentTokenEntity
{
    use ObjectIdentityEmbeddableTrait;
    use ObjectAuditEmbeddableTrait;
    use ObjectSoftDeleteEmbeddableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PaymentMethodEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PaymentMethodEntity $method;

    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    private ?string $tokenHash = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    public function __construct(PaymentMethodEntity $method, ?string $tokenHash = null, ?\DateTimeImmutable $expiresAt = null)
    {
        $this->method = $method;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $expiresAt ?? (new \DateTimeImmutable())->modify('+1 hour');
        $this->initializeObjectIdentity();
        $this->initializeObjectAudit();
        $this->initializeObjectSoftDelete();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function method(): PaymentMethodEntity
    {
        return $this->method;
    }

    public function setMethod(PaymentMethodEntity $method): void
    {
        $this->method = $method;
    }

    public function tokenHash(): ?string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(?string $tokenHash): void
    {
        $this->tokenHash = $tokenHash;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}
