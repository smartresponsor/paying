<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentConsoleFinalizeRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $paymentId = '';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['internal', 'stripe'])]
    public string $provider = 'internal';

    #[Assert\Length(max: 128)]
    public string $providerRef = '';

    #[Assert\Length(max: 64)]
    public string $gatewayTransactionId = '';

    #[Assert\Length(max: 32)]
    public string $status = '';
}
