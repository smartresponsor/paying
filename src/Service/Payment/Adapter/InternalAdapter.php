<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment\Adapter;

use App\ServiceInterface\Payment\GatewayPortInterface;
use App\InfrastructureInterface\Payment\PaymentRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Domain\Payment\Event\PaymentEvent;
use App\Entity\Payment\Payment;
use App\Domain\Payment\PaymentStatus;
use Symfony\Component\Uid\Ulid;

class InternalAdapter implements GatewayPortInterface
{
    public function __construct(private readonly PaymentRepositoryInterface $repo, private readonly EventDispatcherInterface $events) {}

    public function start(Payment $payment, array $context = []): array
    {
        // No external redirect; mark as processing and persist.
        $payment->withStatus(PaymentStatus::processing);
        $this->repo->save($payment);
        $this->events->dispatch(new PaymentEvent('payment.success'), 'payment.success');
        return ['status' => 'processing', 'id' => (string)$payment->id()];
    }

    public function finalize(Ulid $id, array $payload = []): Payment
    {
        $p = $this->repo->findById($id);
        if (!$p) {
            throw new \RuntimeException("Payment not found");
        }
        $p->withStatus(PaymentStatus::completed);
        $this->repo->save($p);
        $this->events->dispatch(new PaymentEvent('payment.success'), 'payment.success');
        return $p;
    }

    public function refund(Ulid $id, string $amount): Payment
    {
        $p = $this->repo->findById($id);
        if (!$p) {
            throw new \RuntimeException("Payment not found");
        }
        $p->withStatus(PaymentStatus::refunded);
        $this->repo->save($p);
        $this->events->dispatch(new PaymentEvent('payment.success'), 'payment.success');
        return $p;
    }

    public function reconcile(Ulid $id): Payment
    {
        $p = $this->repo->findById($id);
        if (!$p) {
            throw new \RuntimeException("Payment not found");
        }
        return $p;
    }
}
