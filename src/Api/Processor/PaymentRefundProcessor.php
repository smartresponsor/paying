<?php
namespace OrderComponent\Payment\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use OrderComponent\Payment\Api\Dto\PaymentRefundInput;
use OrderComponent\Payment\Message\Command\Payment\PaymentRefundCommand;

final class PaymentRefundProcessor implements ProcessorInterface
{
    public function __construct(private MessageBusInterface $bus) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var PaymentRefundInput $data */
        $this->bus->dispatch(new PaymentRefundCommand(
            $data->paymentId, $data->amountMinor, $data->currency, $data->reason, $data->idempotencyKey
        ));
        return ['status' => 'accepted'];
    }
}