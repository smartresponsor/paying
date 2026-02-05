<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\DomainInterface\Payment;

interface PaymentRefundProcessorInterface
{
    public function __construct(private MessageBusInterface $bus);
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed;
}
