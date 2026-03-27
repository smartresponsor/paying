<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

interface PublisherTransportInterface
{
    /** @param array<string, mixed> $payload */
    public function publish(string $topic, array $payload): void;
}
