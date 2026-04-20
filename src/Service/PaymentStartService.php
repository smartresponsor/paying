<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\Payment;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentStartServiceInterface;
use App\Paying\ServiceInterface\ProviderGuardInterface;
use App\Paying\ValueObject\Money;
use App\Paying\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the payment start service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentStartService implements PaymentStartServiceInterface
{
    public function __construct(
        private ProviderGuardInterface $guard,
        private PaymentRepositoryInterface $repo,
    ) {
    }

    /**
     * Executes the start operation for the current payment workflow.
     */
    public function start(string $orderId, string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult
    {
        $money = Money::fromDecimalString($amount, strtoupper($currency));

        $payment = new Payment(new Ulid(), PaymentStatus::new, $money->toDecimalString(), $money->currency(), $orderId);
        $this->repo->save($payment);

        return $this->startExistingPayment($payment, $provider, $idempotencyKey, $origin);
    }

    /**
     * Provides the restart behavior for the payment start service component.
     */
    public function restart(string $paymentId, string $provider, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult
    {
        $existing = $this->repo->find($paymentId);
        if (null === $existing) {
            throw PaymentNotFoundException::byId($paymentId);
        }

        if (PaymentStatus::failed !== $existing->status()) {
            throw new \InvalidArgumentException('Only failed payments can be restarted.');
        }

        return $this->startExistingPayment($existing, $provider, $idempotencyKey, $origin);
    }

    private function startExistingPayment(Payment $payment, string $provider, string $idempotencyKey, string $origin): PaymentStartResult
    {
        try {
            $providerResult = $this->guard->start($provider, $payment, [
                'idempotencyKey' => '' !== $idempotencyKey ? $idempotencyKey : (string) $payment->id(),
                'projectId' => (string) $payment->id(),
                'origin' => $origin,
            ]);
        } catch (\Throwable $exception) {
            $payment->markFailed();
            $this->repo->save($payment);

            throw $exception;
        }

        $providerRef = isset($providerResult['providerRef']) ? (string) $providerResult['providerRef'] : null;
        $payment->markProcessing($providerRef);
        $this->repo->save($payment);

        return new PaymentStartResult($payment, $providerRef, $providerResult);
    }
}
