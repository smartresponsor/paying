<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use App\ValueObject\Payment\PaymentStatus;
use Symfony\Component\Uid\Ulid;

final class InternalPaymentProvider implements PaymentProviderInterface
{
    public function start(Payment $payment, array $context = []): array
    {
        return [
            'provider' => 'internal',
            'paymentId' => (string) $payment->id(),
            'status' => $payment->status()->value,
            'accepted' => true,
        ];
    }

    public function finalize(Ulid $id, array $payload = []): Payment
    {
        return new Payment($id, PaymentStatus::completed, (string) ($payload['amount'] ?? '0.00'), (string) ($payload['currency'] ?? 'USD'));
    }

    public function refund(Ulid $id, string $amount): Payment
    {
        return new Payment($id, PaymentStatus::refunded, $amount, 'USD');
    }

    public function reconcile(Ulid $id): Payment
    {
        return new Payment($id, PaymentStatus::processing, '0.00', 'USD');
    }
}
