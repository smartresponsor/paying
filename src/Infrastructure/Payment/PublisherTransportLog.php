<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

class PublisherTransportLog implements PublisherTransportInterface
{
    public function publish(string $topic, array $payload): void
    {
        // Minimal transport: log to stdout (Docker/Pod logs)
        error_log('[outbox] topic='.$topic.' payload='.json_encode($payload));
    }
}
