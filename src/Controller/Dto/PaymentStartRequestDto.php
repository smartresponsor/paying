<?php

declare(strict_types=1);

namespace App\Paying\Controller\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carries the operator or API request payload required to start a provider-backed payment flow.
 *
 * The DTO intentionally keeps request-facing scalars loose so malformed transport input can be
 * converted into validation responses instead of triggering runtime type errors during binding.
 */
final class PaymentStartRequestDto
{
    /**
     * Identifies the business order that the payment flow should be created for.
     */
    #[Assert\NotBlank]
    public ?string $orderId = null;

    /**
     * Selects the provider strategy that should handle the payment start transition.
     */
    #[Assert\NotBlank]
    public ?string $provider = null;

    /**
     * Carries an optional upstream provider reference when the flow originates from an external surface.
     */
    public ?string $providerRef = null;

    /**
     * Keeps the user-supplied amount in request-safe scalar form until validation and normalization complete.
     */
    public ?string $amount = null;

    /**
     * Carries the ISO currency code associated with the requested payment amount.
     */
    public ?string $currency = null;
}
