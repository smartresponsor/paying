<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a configured payment method code exposed by the component.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_method')]
class PaymentMethod
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 32)]
    private string $code;

    public function __construct(string $id, string $code)
    {
        $this->id = $id;
        $this->code = $code;
    }

    /**
     * Returns the stable payment-method identifier used in persistence.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the short method code used by UI and orchestration layers.
     */
    public function code(): string
    {
        return $this->code;
    }
}
