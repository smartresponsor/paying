<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\Controller\Dto\PaymentStartRequestDto;
use App\Entity\Payment;
use App\ServiceInterface\PaymentApiStartHandlerInterface;
use App\ServiceInterface\PaymentStartServiceInterface;

final class PaymentApiStartHandler implements PaymentApiStartHandlerInterface
{
    public function __construct(
        private readonly PaymentStartServiceInterface $paymentStartService,
        private readonly IdempotencyService $idem,
    ) {
    }

    public function handle(PaymentStartRequestDto $dto, string $idempotencyKey, string $payloadHash): array
    {
        return $this->idem->execute($idempotencyKey, $payloadHash, function () use ($dto, $idempotencyKey): array {
            $started = $this->paymentStartService->start($dto->provider, $dto->amount, $dto->currency, $idempotencyKey, 'api');
            $payment = $started['payment'] ?? null;

            if (!$payment instanceof Payment) {
                throw new \RuntimeException('Payment start response does not contain payment entity.');
            }

            return [
                'payment' => (string) $payment->id(),
                'provider' => $dto->provider,
                'status' => $payment->status()->value,
                'providerRef' => $started['providerRef'] ?? null,
                'result' => $started['result'] ?? [],
            ];
        });
    }
}
