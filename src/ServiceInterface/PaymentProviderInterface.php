<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;
use Symfony\Component\Uid\Ulid;

interface PaymentProviderInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function start(Payment $payment, array $context = []): array;

    /** @param array<string, mixed> $payload */
    public function finalize(Ulid $id, array $payload = []): Payment;

    public function refund(Ulid $id, string $amount): Payment;

    public function reconcile(Ulid $id): Payment;
}
