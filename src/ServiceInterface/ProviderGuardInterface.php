<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;
use Symfony\Component\Uid\Ulid;

interface ProviderGuardInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function start(string $provider, Payment $payment, array $context = []): array;

    /** @param array<string, mixed> $payload */
    public function finalize(string $provider, Ulid $id, array $payload = []): Payment;

    public function refund(string $provider, Ulid $id, string $amount): Payment;

    public function reconcile(string $provider, Ulid $id): Payment;
}
