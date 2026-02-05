<?php
namespace OrderComponent\Payment\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_method')]
class PaymentMethod
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 32)]
    private string $code; // 'card', 'paypal', 'stripe'

    public function __construct(string $id, string $code)
    {
        $this->id = $id;
        $this->code = $code;
    }
}
