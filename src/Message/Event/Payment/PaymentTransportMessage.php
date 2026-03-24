<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Message\Event\Payment;

final class PaymentTransportMessage
{
    public function __construct(
        public readonly string $type,
        /** @var array<string, mixed> */
        public readonly array $payload,
    ) {
    }
}
