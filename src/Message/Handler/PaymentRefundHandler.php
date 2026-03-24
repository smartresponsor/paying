<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Message\Handler;
use App\Message\Command\PaymentRefundCommand;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\Gateway\PaymentGatewayInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PaymentRefundHandler
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repo,
        /** @var iterable<PaymentGatewayInterface> */
        private readonly iterable $gateways,
    ) {
    }

    public function __invoke(PaymentRefundCommand $command): void
    {
        $payment = $this->repo->find($command->paymentId);
        if (null === $payment) {
            throw new \RuntimeException('Payment not found');
        }

        $gateway = $this->selectGatewayByPayment($payment->providerRef());
        $refundRef = $gateway->refund((string) $payment->id(), $command->amountMinor, $command->currency, $command->reason);

        $payment->markRefunded($refundRef);

        $this->repo->save($payment);
    }

    private function selectGatewayByPayment(?string $providerRef): PaymentGatewayInterface
    {
        $code = $this->resolveGatewayCode($providerRef);

        foreach ($this->gateways as $gateway) {
            if ($gateway->code() === $code) {
                return $gateway;
            }
        }

        throw new \RuntimeException('Gateway for payment not found: '.$code);
    }

    private function resolveGatewayCode(?string $providerRef): string
    {
        if (is_string($providerRef) && str_starts_with($providerRef, 'paypal_')) {
            return 'paypal';
        }

        return 'stripe';
    }
}
