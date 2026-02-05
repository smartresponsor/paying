<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\DomainInterface\Payment;

interface PaymentRefundHandlerInterface
{
    public function __construct(private PaymentRepositoryInterface $repo, private EntityManagerInterface $em, /** @var iterable<PaymentGatewayInterface> */ private iterable $gateways);
    public function __invoke(PaymentRefundCommand $c): void;
}
