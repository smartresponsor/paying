<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Message\Handler;

use App\Paying\Message\Command\PaymentRefundCommand;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\RefundServiceInterface;
use App\Paying\ValueObject\Money;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

/**
 * Handles the payment refund handler workflow inside the payment messenger pipeline.
 */
#[AsMessageHandler]
/**
 * Handles the payment refund handler workflow inside the payment messenger pipeline.
 */
final readonly class PaymentRefundHandler
{
    public function __construct(
        private PaymentRepositoryInterface $repo,
        private RefundServiceInterface $refundService,
    ) {
    }

    /**
     * Executes the message handling workflow for the current payment transport message.
     */
    public function __invoke(PaymentRefundCommand $command): void
    {
        $payment = $this->repo->find($command->paymentId);
        if (null === $payment) {
            throw new \RuntimeException('Payment not found');
        }

        $amount = Money::fromMinor($command->amountMinor, strtoupper($command->currency))->toDecimalString();
        $provider = $this->resolveProvider($payment->providerRef());

        $this->refundService->refund(new Ulid($command->paymentId), $amount, $provider);
    }

    private function resolveProvider(?string $providerRef): string
    {
        if (!is_string($providerRef) || '' === trim($providerRef)) {
            return 'internal';
        }

        $normalized = strtolower($providerRef);

        if (str_starts_with($normalized, 'paypal_')) {
            return 'paypal';
        }

        if (str_starts_with($normalized, 'stripe_') || str_starts_with($normalized, 'cs_')) {
            return 'stripe';
        }

        return 'internal';
    }
}
