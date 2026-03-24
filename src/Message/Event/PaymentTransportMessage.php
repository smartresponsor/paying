<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Message\Event;

final class PaymentTransportMessage
{
    public function __construct(
        public readonly string $type,
        /** @var array<string, mixed> */
        public readonly array $payload,
    ) {
    }
}
