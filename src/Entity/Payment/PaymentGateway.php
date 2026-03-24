<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Entity\Payment;

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
