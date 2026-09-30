<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carries validated identifier input for reading a payment aggregate.
 */
final class PaymentReadRequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $id = '';
}
