<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

interface ProviderGuardInterface
{
    /** @return array<string, mixed> */
    public function start(string $provider, Payment $payment, array $context = []): array;

    public function finalize(string $provider, Ulid $id, array $payload = []): Payment;

    public function refund(string $provider, Ulid $id, string $amount): Payment;

    public function reconcile(string $provider, Ulid $id): Payment;
}
