<?php

declare(strict_types=1);

namespace App\Paying\Entity;

use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectIdentityEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Localized payment copy restored from the old PaymentEnUs monolith entity.
 *
 * Locale-specific class names are intentionally not preserved; translations are modeled
 * as data rows keyed by locale.
 */
#[ORM\Entity(repositoryClass: \App\Paying\Repository\PaymentTranslationRepository::class)]
#[ORM\Table(name: 'payment_translation', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_payment_translation_payment_locale', columns: ['payment_id', 'locale'])])]
class PaymentTranslationEntity
{
    use ObjectIdentityEmbeddableTrait;
    use ObjectAuditEmbeddableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PaymentEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PaymentEntity $payment;

    #[ORM\Column(type: 'string', length: 16)]
    private string $locale;

    #[ORM\Column(type: 'string', length: 160, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct(PaymentEntity $payment, string $locale)
    {
        $this->payment = $payment;
        $this->locale = strtolower($locale);
        $this->initializeObjectIdentity();
        $this->initializeObjectAudit();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function payment(): PaymentEntity
    {
        return $this->payment;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
