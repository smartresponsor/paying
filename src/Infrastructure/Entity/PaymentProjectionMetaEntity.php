<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stores payment projection metadata such as the replay watermark.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_projection_meta')]
class PaymentProjectionMetaEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 80)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 64)]
    private string $value = '';

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
