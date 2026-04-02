<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Handler;

use App\Message\Command\PaymentCreateCommand;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ValueObject\Money;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class PaymentCreateHandler
{
    public function __construct(private PaymentStartServiceInterface $paymentStartService)
    {
    }

    public function __invoke(PaymentCreateCommand $command): void
    {
        $money = Money::fromMinor($command->amountMinor, strtoupper($command->currency));

        $this->paymentStartService->start(
            $command->orderId,
            $this->normalizeProvider($command->providerCode),
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
            default => throw new \RuntimeException('Payment provider not found: '.$providerCode),
        };
    }
}
