<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment\Adapter;

use App\ServiceInterface\Payment\GatewayPortInterface;
use App\InfrastructureInterface\Payment\PaymentRepositoryInterface;
use App\Entity\Payment\Payment;
use App\Domain\Payment\PaymentStatus;
use Symfony\Component\Uid\Ulid;

class AdyenAdapter implements GatewayPortInterface
{
    public function __construct(private readonly PaymentRepositoryInterface $repo) {}

    public function start(Payment $payment, array $context = []): array
    {
        $payment->withStatus(PaymentStatus::processing);
        $this->repo->save($payment);
        return ['provider' => 'adyen', 'status' => 'processing', 'id' => (string)$payment->id()];
    }

    public function finalize(Ulid $id, array $payload = []): Payment
    {
        $p = $this->repo->findById($id);
        if (!$p) throw new \RuntimeException('Payment not found');
        $p->withStatus(PaymentStatus::completed);
        $this->repo->save($p);
        return $p;
    }

    public function refund(Ulid $id, string $amount): Payment
    {
        $p = $this->repo->findById($id);
        if (!$p) throw new \RuntimeException('Payment not found');
        $p->withStatus(PaymentStatus::refunded);
        $this->repo->save($p);
        return $p;
    }

    public function reconcile(Ulid $id): Payment
    {
        $p = $this->repo->findById($id);
        if (!$p) throw new \RuntimeException('Payment not found');
        return $p;
    }
}
