<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use App\InfrastructureInterface\Payment\PublisherTransportInterface;

class PublisherTransportLog implements PublisherTransportInterface
{
    public function publish(string $topic, array $payload): void
    {
        // Minimal transport: log to stdout (Docker/Pod logs)
        error_log('[outbox] topic=' . $topic . ' payload=' . json_encode($payload));
    }
}
