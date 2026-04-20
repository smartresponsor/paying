<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Message\Event;

/**
 * Represents the payment transport message notification emitted by the payment messaging layer.
 */
final readonly class PaymentTransportMessage
{
    public function __construct(
        public string $type,
        /** @var array<string, mixed> */
        public array $payload,
    ) {
    }
}
