<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentReadRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $id = '';
}
