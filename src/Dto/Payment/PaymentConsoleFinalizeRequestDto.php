<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Dto\Payment;

use App\Paying\ValueObject\PaymentStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Carries validated finalize command input from the operator console workflow.
 */
final class PaymentConsoleFinalizeRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $paymentId = '';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['internal', 'stripe', 'paypal'])]
    public string $provider = 'internal';

    #[Assert\Length(max: 128)]
    public string $providerRef = '';

    #[Assert\Length(max: 64)]
    public string $providerTransactionId = '';

    #[Assert\Length(max: 32)]
    public string $status = '';

    #[Assert\Callback]
    /**
     * Rejects console finalize requests that carry a payment status outside the supported enum set.
     */
    public function validateStatus(ExecutionContextInterface $context): void
    {
        if ('' === $this->status) {
            return;
        }

        if (!in_array($this->status, PaymentStatus::values(), true)) {
            $context
                ->buildViolation('Status must be one of: '.implode(', ', PaymentStatus::values()).'.')
                ->atPath('status')
                ->addViolation();
        }
    }
}
