<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\DomainInterface\Payment;

interface PaymentEventConsumerInterface
{
    public function __construct(private PaymentReconciliationService $svc, private ?OrderStatusPortInterface $orderPort = null);
    public function __invoke(object $message): void;
}
