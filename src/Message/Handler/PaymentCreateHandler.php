<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Message\Handler;

use App\Paying\Message\Command\PaymentCreateCommand;
use App\Paying\ServiceInterface\PaymentStartServiceInterface;
use App\Paying\ValueObject\PaymentMoney;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handles the payment create handler workflow inside the payment messenger pipeline.
 */
#[AsMessageHandler]
/**
 * Handles the payment create handler workflow inside the payment messenger pipeline.
 */
final readonly class PaymentCreateHandler
{
    public function __construct(private PaymentStartServiceInterface $paymentStartService)
    {
    }

    /**
     * Executes the message handling workflow for the current payment transport message.
     */
    public function __invoke(PaymentCreateCommand $command): void
    {
        $money = PaymentMoney::fromMinor($command->amountMinor, strtoupper($command->currency));

        $this->paymentStartService->start(
            $command->orderId,
            $this->normalizeProvider($command->canonicalProviderCode()),
            $money->toDecimalString(),
            $money->currency(),
            $command->idempotencyKey ?? '',
            'messenger-create',
        );
    }

    private function normalizeProvider(string $providerCode): string
    {
        $normalized = strtolower(trim($providerCode));

        return match ($normalized) {
            'stripe', 'paypal', 'internal' => $normalized,
            default => throw new \RuntimeException('PaymentEntity provider not found: '.$providerCode),
        };
    }
}
