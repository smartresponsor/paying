<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Dto;

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
