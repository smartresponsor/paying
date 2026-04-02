<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;
use Symfony\Component\Uid\Ulid;

/**
 * Contract implemented by all payment providers.
 *
 * Each provider adapter must translate application-level operations into
 * provider-specific API calls and return normalized results.
 */
interface PaymentProviderInterface
{
    /**
     * Initiates a provider-side payment flow.
     *
     * @param Payment $payment Payment aggregate.
     * @param array<string, mixed> $context Provider execution context.
     *
     * @return array<string, mixed> Normalized provider response.
     */
    public function start(Payment $payment, array $context = []): array;

    /**
     * Finalizes a payment based on provider payload.
     */
    public function finalize(Ulid $id, array $payload = []): Payment;

    /**
     * Refunds a payment partially or fully.
     */
    public function refund(Ulid $id, string $amount): Payment;

    /**
     * Reconciles payment state with provider.
     */
    public function reconcile(Ulid $id): Payment;
}
