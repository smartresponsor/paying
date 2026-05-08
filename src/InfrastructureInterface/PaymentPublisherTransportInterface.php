<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\InfrastructureInterface;

/**
 * Defines the transport contract used by payment message publishers.
 */
interface PaymentPublisherTransportInterface
{
    /**
     * Publishes a serialized message to the configured transport.
     *
     * @param array<string, mixed> $payload
     */
    public function publish(string $topic, array $payload): void;
}
