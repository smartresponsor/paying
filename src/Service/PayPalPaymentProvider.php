<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\Service\Gateway\PayPalGateway;
use App\ServiceInterface\PaymentProviderInterface;
use App\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the pay pal payment provider service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PayPalPaymentProvider implements PaymentProviderInterface
{
    public function __construct(private PayPalGateway $gateway)
    {
    }

    /**
     * Executes the start operation for the current payment workflow.
     */
    public function start(Payment $payment, array $context = []): array
    {
        $amountMinor = (int) round(((float) $payment->amount()) * 100);
        $providerRef = $this->gateway->authorize((string) $payment->id(), $amountMinor, $payment->currency());

        return [
            'provider' => 'paypal',
            'paymentId' => (string) $payment->id(),
            'providerRef' => $providerRef,
            'accepted' => true,
        ];
    }

    /**
     * Provides the finalize behavior for the pay pal payment provider component.
     */
    public function finalize(Ulid $id, array $payload = []): Payment
    {
        $payment = new Payment(
            $id,
            $this->resolveStatus((string) ($payload['status'] ?? PaymentStatus::completed->value)),
            (string) ($payload['amount'] ?? '0.00'),
            (string) ($payload['currency'] ?? 'USD'),
        );

        $providerRef = (string) ($payload['providerRef'] ?? $payload['providerTransactionId'] ?? '');
        if ('' !== $providerRef) {
            $payment->withProviderRef($providerRef);
        }

        return $payment;
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(Ulid $id, string $amount): Payment
    {
        $currency = 'USD';
        $amountMinor = (int) round(((float) $amount) * 100);
        $providerRef = $this->gateway->refund((string) $id, $amountMinor, $currency);

        return new Payment($id, PaymentStatus::refunded, $amount, $currency)->withProviderRef($providerRef);
    }

    /**
     * Provides the reconcile behavior for the pay pal payment provider component.
     */
    public function reconcile(Ulid $id): Payment
    {
        return new Payment($id, PaymentStatus::processing, '0.00', 'USD')->withProviderRef('paypal_reconcile_'.$id);
    }

    private function resolveStatus(string $status): PaymentStatus
    {
        return match (strtolower(trim($status))) {
            PaymentStatus::failed->value => PaymentStatus::failed,
            PaymentStatus::refunded->value => PaymentStatus::refunded,
            PaymentStatus::canceled->value => PaymentStatus::canceled,
            PaymentStatus::processing->value => PaymentStatus::processing,
            PaymentStatus::pending->value => PaymentStatus::pending,
            PaymentStatus::new->value => PaymentStatus::new,
            default => PaymentStatus::completed,
        };
    }
}
