<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO used by the operator console to trigger a refund operation.
 */
final class PaymentConsoleRefundRequestDto
{
    /**
     * Payment identifier.
     */
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $paymentId = '';

    /**
     * Decimal refund amount in major units.
     */
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d{2})$/', message: 'Use decimal amount format like 50.00.')]
    public string $amount = '0.00';

    /**
     * Provider that should process the refund.
     */
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['internal', 'stripe', 'paypal'])]
    public string $provider = 'internal';
}
