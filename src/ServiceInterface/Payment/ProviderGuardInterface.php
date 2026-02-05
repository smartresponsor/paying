<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

interface ProviderGuardInterface
{
    public function start(string $provider, Payment $payment, array $context = []): array;
    public function finalize(string $provider, Ulid $id, array $payload = []): Payment;
    public function refund(string $provider, Ulid $id, string $amount): Payment;
    public function reconcile(string $provider, Ulid $id): Payment;
}
