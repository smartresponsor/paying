<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO used by the operator console to create a payment aggregate.
 *
 * Unlike the public API start DTO, this form uses minor units because it is
 * intended for direct operator input and debugging-oriented workflows.
 */
final class PaymentCreateRequestDto
{
    /**
     * External order identifier that owns the payment.
     */
    #[Assert\NotBlank]
    public string $orderId = '';

    /**
     * Amount in minor units, for example cents.
     */
    #[Assert\Positive]
    public int $amountMinor = 0;

    /**
     * ISO-4217 currency code.
     */
    #[Assert\Currency]
    public string $currency = 'USD';
}
