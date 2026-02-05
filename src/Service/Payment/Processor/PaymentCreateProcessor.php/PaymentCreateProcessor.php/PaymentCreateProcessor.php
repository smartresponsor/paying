<?php
namespace App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use OrderComponent\Payment\Api\Dto\PaymentCreateInput;
use OrderComponent\Payment\Message\Command\Payment\PaymentCreateCommand;

final class PaymentCreateProcessor implements ProcessorInterface
{
    public function __construct(private MessageBusInterface $bus) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var PaymentCreateInput $data */
        $this->bus->dispatch(new PaymentCreateCommand(
            $data->orderId, $data->amountMinor, $data->currency, $data->gatewayCode, $data->idempotencyKey
        ));
        return ['status' => 'accepted'];
    }
}