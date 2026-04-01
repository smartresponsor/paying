<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\Service\PaymentStartResult;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ValueObject\Money;
use App\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

final readonly class PaymentStartService implements PaymentStartServiceInterface
{
    public function __construct(
        private ProviderGuardInterface $guard,
        private PaymentRepositoryInterface $repo,
    ) {
    }

    public function start(string $orderId, string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult
    {
        $money = Money::fromDecimalString($amount, strtoupper($currency));

        $payment = new Payment(new Ulid(), PaymentStatus::new, $money->toDecimalString(), $money->currency(), $orderId);
        $this->repo->save($payment);

        return $this->startExistingPayment($payment, $provider, $idempotencyKey, $origin);
    }

    public function restart(string $paymentId, string $provider, string $idempotencyKey = '', string $origin = 'api'): PaymentStartResult
    {
        $existing = $this->repo->find($paymentId);
        if (null === $existing) {
            throw PaymentNotFoundException::byId($paymentId);
        }

        if ($existing->status() !== PaymentStatus::failed) {
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
