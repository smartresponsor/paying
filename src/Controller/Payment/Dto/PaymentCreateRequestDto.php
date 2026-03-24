<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Payment\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentCreateRequestDto
{
    #[Assert\NotBlank]
    public string $orderId = '';

    #[Assert\Positive]
    public int $amountMinor = 0;

    #[Assert\Currency]
    public string $currency = 'USD';
}
