<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a configured payment gateway code that the component can route work through.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_gateway')]
class PaymentGateway
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
     * Returns the stable gateway identifier used by persistence and integration layers.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the short gateway code used for routing and selection.
     */
    public function code(): string
    {
        return $this->code;
    }
}
