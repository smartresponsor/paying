<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Dto\Payment;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carries validated refund payload data for the public payment API.
 */
final class PaymentRefundRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d{2})$/', message: 'Use decimal amount format like 50.00.')]
    public string $amount = '0.00';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['internal', 'stripe', 'paypal'])]
    public string $provider = 'internal';
}
