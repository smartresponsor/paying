<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Message\Handler;
use App\Entity\Payment;
use App\Message\Command\PaymentCreateCommand;
use App\Repository\PaymentRepositoryInterface;
use App\ServiceInterface\Gateway\PaymentGatewayInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler]
final class PaymentCreateHandler
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repo,
        /** @var iterable<PaymentGatewayInterface> */
        private readonly iterable $gateways,
    ) {
    }

    public function __invoke(PaymentCreateCommand $command): void
    {
        $payment = new Payment(
            new Ulid(),
            PaymentStatus::new,
            number_format($command->amountMinor / 100, 2, '.', ''),
            $command->currency,
        );

        $gateway = $this->selectGateway($command->gatewayCode);
        $providerRef = $gateway->authorize((string) $payment->id(), $command->amountMinor, $command->currency);

        $payment->markProcessing($providerRef);

        $this->repo->save($payment);
    }

    private function selectGateway(string $code): PaymentGatewayInterface
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->code() === $code) {
                return $gateway;
            }
        }

        throw new \RuntimeException('Payment gateway not found: '.$code);
    }
}
