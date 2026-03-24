<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\InfrastructureInterface;

interface PublisherTransportInterface
{
    public function publish(string $topic, array $payload): void;
}
