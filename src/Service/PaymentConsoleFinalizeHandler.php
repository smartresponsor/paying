<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentConsoleFinalizeHandlerInterface;
use App\Paying\ServiceInterface\PaymentProviderGuardInterface;
use App\Paying\ValueObject\PaymentFinalizePayload;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the payment console finalize handler service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentConsoleFinalizeHandler implements PaymentConsoleFinalizeHandlerInterface
{
    public function __construct(
        private PaymentRepositoryInterface $repo,
        private PaymentProviderGuardInterface $guard,
    ) {
    }

    /**
     * Provides the finalize behavior for the payment console finalize handler component.
     */
    public function finalize(
        string $paymentId,
        string $provider,
        ?string $providerRef,
        ?string $providerTransactionId,
        ?string $status,
    ): ?PaymentEntity {
        $payment = $this->repo->find($paymentId);
        if (null === $payment) {
            return null;
        }

        $payload = new PaymentFinalizePayload(
            $providerRef ?? '',
            $providerTransactionId ?? '',
            $status ?? '',
        );

        $resolved = $this->guard->finalize($provider, new Ulid($paymentId), $payload->toProviderPayload());
        $payment->syncFrom($resolved);
        $this->repo->save($payment);

        return $payment;
    }
}
