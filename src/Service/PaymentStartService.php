<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ValueObject\PaymentStatus;
use Symfony\Component\Uid\Ulid;

final class PaymentStartService implements PaymentStartServiceInterface
{
    public function __construct(
        private readonly ProviderGuardInterface $guard,
        private readonly PaymentRepositoryInterface $repo,
    ) {
    }

    public function start(string $provider, string $amount, string $currency, string $idempotencyKey = '', string $origin = 'api'): array
    {
        $payment = new Payment(new Ulid(), PaymentStatus::new, $amount, $currency);
        $this->repo->save($payment);

        $providerResult = $this->guard->start($provider, $payment, [
            'idempotencyKey' => '' !== $idempotencyKey ? $idempotencyKey : (string) $payment->id(),
            'projectId' => (string) $payment->id(),
            'origin' => $origin,
        ]);

        $providerRef = isset($providerResult['providerRef']) ? (string) $providerResult['providerRef'] : null;
        $payment->markProcessing($providerRef);
        $this->repo->save($payment);

        return [
            'payment' => $payment,
            'providerRef' => $providerRef,
            'result' => is_array($providerResult) ? $providerResult : [],
        ];
    }
}
