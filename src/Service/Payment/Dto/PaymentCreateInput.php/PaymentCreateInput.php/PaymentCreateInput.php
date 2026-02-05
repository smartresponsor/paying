<?php
namespace App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentCreateInput
{
    #[Assert\NotBlank] public string $orderId;
    #[Assert\Positive] public int $amountMinor;
    #[Assert\Currency] public string $currency;
    #[Assert\Choice(['stripe','paypal','authorize'])] public string $gatewayCode;
    public ?string $idempotencyKey = null;
}