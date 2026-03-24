<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_gateway')]
class PaymentGateway
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 32)]
    private string $code; // 'stripe','paypal','authorize'

    public function __construct(string $id, string $code)
    {
        $this->id = $id;
        $this->code = $code;
    }
}
