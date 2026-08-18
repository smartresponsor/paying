<?php

declare(strict_types=1);

namespace App\Paying\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a configured payment gateway code that the component can route work through.
 *
 * Entity-first reconciliation: restored the old monolith gateway metadata and the
 * gateway-to-method relation without bringing ApiPlatform/controller legacy back.
 */
#[ORM\Entity(repositoryClass: \App\Paying\Repository\PaymentGatewayRepository::class)]
#[ORM\Table(name: 'payment_gateway')]
class PaymentGatewayEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'guid', unique: true)]
    private string $slug;

    #[ORM\Column(type: 'string', length: 32)]
    private string $code;

    /** @var list<string> */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $currencies = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $sandboxMode = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $sortOrder = 0;

    /** @var Collection<int, PaymentMethodEntity> */
    #[ORM\OneToMany(mappedBy: 'gateway', targetEntity: PaymentMethodEntity::class, cascade: ['persist'])]
    private Collection $paymentMethods;

    public function __construct(string $id, string $code)
    {
        $this->slug = $id;
        $this->code = $code;
        $this->paymentMethods = new ArrayCollection();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function code(): string
    {
        return $this->code;
    }

    /** @return list<string> */
    public function currencies(): array
    {
        return $this->currencies ?? [];
    }

    /** @param list<string> $currencies */
    public function setCurrencies(array $currencies): void
    {
        $this->currencies = array_values($currencies);
    }

    public function sandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    public function setSandboxMode(bool $sandboxMode): void
    {
        $this->sandboxMode = $sandboxMode;
    }

    public function logoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /** @return Collection<int, PaymentMethodEntity> */
    public function paymentMethods(): Collection
    {
        return $this->paymentMethods;
    }

    public function addPaymentMethod(PaymentMethodEntity $method): void
    {
        if (!$this->paymentMethods->contains($method)) {
            $this->paymentMethods->add($method);
            $method->setGateway($this);
        }
    }

    public function removePaymentMethod(PaymentMethodEntity $method): void
    {
        if ($this->paymentMethods->removeElement($method) && $method->gateway() === $this) {
            $method->setGateway(null);
        }
    }
}
