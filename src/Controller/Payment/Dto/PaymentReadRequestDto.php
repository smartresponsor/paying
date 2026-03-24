<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Payment\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentReadRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $id = '';
}
