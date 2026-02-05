<?php
namespace App\Domain\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\Domain\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\Message\Handler\Payment;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Contract\RepositoryInterface\Payment\PaymentRepositoryInterface;
use OrderComponent\Payment\Contract\ServiceInterface\Payment\PaymentGatewayInterface;
use OrderComponent\Payment\Message\Command\Payment\PaymentRefundCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PaymentRefundHandler
{
    public function __construct(
        private PaymentRepositoryInterface $repo,
        private EntityManagerInterface $em,
        /** @var iterable<PaymentGatewayInterface> */
        private iterable $gateways
    ) {}

    public function __invoke(PaymentRefundCommand $c): void
    {
        $payment = $this->repo->find($c->paymentId);
        if (!$payment) {
            throw new \RuntimeException('Payment not found');
        }
        $gateway = $this->selectGatewayByPayment($payment);
        $gateway->refund($payment->id(), $c->amountMinor, $c->currency, $c->reason);
        $this->em->flush();
    }

    private function selectGatewayByPayment(object $payment): PaymentGatewayInterface
    {
        // naive mapping by method; in real impl persist gateway code on Payment
        $code = 'stripe';
        foreach ($this->gateways as $g) {
            if (method_exists($g, 'code') and $g->code() === $code) {
                return $g;
            }
        }
        throw new \RuntimeException('Gateway for payment not found');
    }
}