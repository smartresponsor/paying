<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\InfrastructureInterface\PublisherTransportInterface;

class PublisherTransportLog implements PublisherTransportInterface
{
    /** @param array<string, mixed> $payload */
    public function publish(string $topic, array $payload): void
    {
        error_log('[outbox] topic='.$topic.' payload='.json_encode($payload));
    }
}
