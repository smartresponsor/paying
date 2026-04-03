<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Dto;

use App\ValueObject\PaymentStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PaymentFinalizeRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['internal', 'stripe', 'paypal'])]
    public string $provider = 'internal';

    #[Assert\Length(max: 128)]
    public string $providerRef = '';

    #[Assert\Length(max: 64)]
    public string $gatewayTransactionId = '';

    #[Assert\Length(max: 32)]
    public string $status = '';

    #[Assert\Callback]
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
