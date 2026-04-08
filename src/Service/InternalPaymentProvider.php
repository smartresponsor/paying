<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\PaymentProviderInterface;
use App\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the internal payment provider service used by the payment lifecycle and operator-facing flows.
 */
final class InternalPaymentProvider implements PaymentProviderInterface
{
    /**
     * Executes the start operation for the current payment workflow.
     *
     * @return array<string, mixed>
     */
    public function start(Payment $payment, array $context = []): array
    {
        return [
            'provider' => 'internal',
            'paymentId' => (string) $payment->id(),
            'status' => $payment->status()->value,
            'accepted' => true,
        ];
    }

    /**
     * Provides the finalize behavior for the internal payment provider component.
     */
    public function finalize(Ulid $id, array $payload = []): Payment
    {
        return new Payment($id, PaymentStatus::completed, (string) ($payload['amount'] ?? '0.00'), (string) ($payload['currency'] ?? 'USD'));
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(Ulid $id, string $amount): Payment
    {
        return new Payment($id, PaymentStatus::refunded, $amount, 'USD');
    }

    /**
     * Provides the reconcile behavior for the internal payment provider component.
     */
    public function reconcile(Ulid $id): Payment
    {
        return new Payment($id, PaymentStatus::processing, '0.00', 'USD');
    }
}
