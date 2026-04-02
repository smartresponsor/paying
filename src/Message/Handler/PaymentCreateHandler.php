<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Handler;

use App\Message\Command\PaymentCreateCommand;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ValueObject\Money;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Message handler responsible for translating a create command into a start-service call.
 *
 * Responsibilities:
 * - normalize amount and currency
 * - resolve canonical provider identifier
 * - pass execution to {@see PaymentStartServiceInterface}
 * - provide a stable origin marker for observability
 */
#[AsMessageHandler]
final readonly class PaymentCreateHandler
{
    public function __construct(private PaymentStartServiceInterface $paymentStartService)
    {
    }

    /**
     * Handles the message bus invocation.
     */
    public function __invoke(PaymentCreateCommand $command): void
    {
        $money = Money::fromMinor($command->amountMinor, strtoupper($command->currency));

        $this->paymentStartService->start(
            $command->orderId,
            $this->normalizeProvider($command->canonicalProviderCode()),
            $money->toDecimalString(),
            $money->currency(),
            $command->idempotencyKey ?? '',
            'messenger-create',
        );
    }

    /**
     * Normalizes and validates provider identifiers.
     *
     * @throws \RuntimeException When provider is not supported.
     */
    private function normalizeProvider(string $providerCode): string
    {
        $normalized = strtolower(trim($providerCode));

        return match ($normalized) {
            'stripe', 'paypal', 'internal' => $normalized,
            default => throw new \RuntimeException('Payment provider not found: '.$providerCode),
        };
    }
}
