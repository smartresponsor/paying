<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

interface PaymentProviderInterface
{
    /** @return array<string, mixed> */
    public function start(Payment $payment, array $context = []): array;

    public function finalize(Ulid $id, array $payload = []): Payment;

    public function refund(Ulid $id, string $amount): Payment;

    public function reconcile(Ulid $id): Payment;
}
