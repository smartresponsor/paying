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

space OrderComponent\Payment\Message\Event\Payment;

final class PaymentFailedEvent
{
    public function __construct(
        public string $paymentId,
        public string $errorCode,
        public ?string $message = null
    ) {}
}
