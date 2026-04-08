<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

/**
 * Defines the contract for publishing payment outbox messages.
 */
interface OutboxPublisherInterface
{
    /**
     * Queues a transport message for asynchronous publication.
     *
     * @param array<string, mixed> $payload
     */
    public function enqueue(string $topic, array $payload): void;

    /**
     * Moves a failed transport message into the dead-letter queue.
     */
    public function moveToDlq(string $id, string $reason): void;
}
