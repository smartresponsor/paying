<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentConsoleFinalizeHandlerInterface;
use App\ServiceInterface\ProviderGuardInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleFinalizeHandler implements PaymentConsoleFinalizeHandlerInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repo,
        private readonly ProviderGuardInterface $guard,
    ) {
    }

    public function finalize(
        string $paymentId,
        string $provider,
        ?string $providerRef,
        ?string $gatewayTransactionId,
        ?string $status,
    ): ?Payment {
        $payment = $this->repo->find($paymentId);
        if (null === $payment) {
            return null;
        }

        $payload = array_filter([
            'providerRef' => $providerRef,
            'gatewayTransactionId' => $gatewayTransactionId,
            'status' => $status,
        ], static fn (mixed $value): bool => is_string($value) && '' !== $value);

        $resolved = $this->guard->finalize($provider, new Ulid($paymentId), $payload);
        $payment->syncFrom($resolved);
        $this->repo->save($payment);

        return $payment;
    }
}
