<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\IdempotencyServiceInterface;
use App\ServiceInterface\PaymentApiStartHandlerInterface;
use App\ServiceInterface\PaymentStartInput;
use App\ServiceInterface\PaymentStartServiceInterface;

final readonly class PaymentApiStartHandler implements PaymentApiStartHandlerInterface
{
    public function __construct(
        private PaymentStartServiceInterface $paymentStartService,
        private IdempotencyServiceInterface $idem,
    ) {
    }

    /**
     * @throws \JsonException
     */
    /**
     * @throws \JsonException
     */
    public function handle(PaymentStartInput $input, string $idempotencyKey, string $payloadHash): array
    {
        return $this->idem->execute($idempotencyKey, $payloadHash, function () use ($input, $idempotencyKey): array {
            $started = $this->paymentStartService->start($input->provider, $input->amount, $input->currency, $idempotencyKey);

            return [
                'payment' => (string) $started->payment->id(),
                'provider' => $input->provider,
                'status' => $started->payment->status()->value,
                'providerRef' => $started->providerRef,
                'result' => $started->providerResult,
            ];
        });
    }
}
