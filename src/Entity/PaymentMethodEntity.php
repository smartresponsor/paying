<?php

declare(strict_types=1);

namespace App\Paying\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a configured payment method code exposed by the component.
 *
 * Entity-first reconciliation: restored the old monolith method nameEntity and gateway relation.
 */
#[ORM\Entity(repositoryClass: \App\Paying\Repository\PaymentMethodRepository::class)]
#[ORM\Table(name: 'payment_method')]
class PaymentMethodEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'guid', unique: true)]
    private string $slug;

    #[ORM\Column(type: 'string', length: 32)]
    private string $code;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $methodName = null;

    #[ORM\ManyToOne(targetEntity: PaymentGatewayEntity::class, inversedBy: 'paymentMethods')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PaymentGatewayEntity $gateway = null;

    public function __construct(string $id, string $code, ?string $methodName = null)
    {
        $this->slug = $id;
        $this->code = $code;
        $this->methodName = $methodName;
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

    public function methodName(): ?string
    {
        return $this->methodName;
    }

    public function setMethodName(?string $methodName): void
    {
        $this->methodName = $methodName;
    }

    public function gateway(): ?PaymentGatewayEntity
    {
        return $this->gateway;
    }

    public function setGateway(?PaymentGatewayEntity $gateway): void
    {
        $this->gateway = $gateway;
    }
}
