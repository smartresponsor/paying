<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Message\Event;

final readonly class PaymentTransportMessage
{
    public function __construct(
        public string $type,
        /** @var array<string, mixed> */
        public array $payload,
    ) {
    }
}
