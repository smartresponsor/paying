<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

interface GatewayPortInterface
{
    public function start(Payment $payment, array $context = []): array;
    public function finalize(Ulid $id, array $payload = []): Payment;
    public function refund(Ulid $id, string $amount): Payment;
    public function reconcile(Ulid $id): Payment;
}
