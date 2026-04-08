<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carries validated identifier input for reading a payment aggregate.
 */
final class PaymentReadRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $id = '';
}
