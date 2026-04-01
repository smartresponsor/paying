<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentConsoleFinalizeHandlerInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ValueObject\PaymentFinalizePayload;
use Symfony\Component\Uid\Ulid;

final readonly class PaymentConsoleFinalizeHandler implements PaymentConsoleFinalizeHandlerInterface
{
    public function __construct(
        private PaymentRepositoryInterface $repo,
        private ProviderGuardInterface $guard,
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

        $payload = new PaymentFinalizePayload(
            $providerRef ?? '',
            $gatewayTransactionId ?? '',
            $status ?? '',
        );

        $resolved = $this->guard->finalize($provider, new Ulid($paymentId), $payload->toProviderPayload());
        $payment->syncFrom($resolved);
        $this->repo->save($payment);

        return $payment;
    }
}
