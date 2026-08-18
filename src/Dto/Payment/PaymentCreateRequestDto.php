<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Dto\Payment;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Captures validated input for creating a new payment aggregate.
 */
final class PaymentCreateRequestDto
{
    #[Assert\NotBlank]
    public string $orderId = '';

    #[Assert\Positive]
    public int $amountMinor = 0;

    #[Assert\Currency]
    public string $currency = 'USD';
}
