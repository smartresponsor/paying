<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure\Payment;

class PublisherTransportLog implements PublisherTransportInterface
{
    public function publish(string $topic, array $payload): void
    {
        // Minimal transport: log to stdout (Docker/Pod logs)
        error_log('[outbox] topic='.$topic.' payload='.json_encode($payload));
    }
}
