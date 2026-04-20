<?php

declare(strict_types=1);

namespace App\Paying\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentStartRequestDto
{
    #[Assert\NotBlank]
    public ?string $orderId = null;

    #[Assert\NotBlank]
    public ?string $provider = null;

    public ?string $providerRef = null;

    /**
     * Keep scalar-loose for request/form binding; validation should convert this
     * into 422 instead of triggering runtime type errors for malformed payloads.
     */
    public ?string $amount = null;

    public ?string $currency = null;
}
