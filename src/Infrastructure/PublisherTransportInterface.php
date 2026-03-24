<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure;

interface PublisherTransportInterface
{
    public function publish(string $topic, array $payload): void;
}
