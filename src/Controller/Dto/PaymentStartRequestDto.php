<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentStartRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d{2})$/', message: 'Use decimal amount format like 50.00.')]
    public string $amount = '0.00';

    #[Assert\Currency]
    public string $currency = 'USD';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['internal', 'stripe'])]
    public string $provider = 'internal';
}
