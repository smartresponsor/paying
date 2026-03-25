<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\InfrastructureInterface;

interface OutboxPublisherInterface
{
    /** @param array<string, mixed> $payload */
    public function enqueue(string $topic, array $payload): void;

    public function moveToDlq(string $id, string $reason): void;
}
