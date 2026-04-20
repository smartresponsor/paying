<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\InfrastructureInterface\PublisherTransportInterface;

/**
 * Persists transport publish attempts for payment messaging observability.
 */
class PublisherTransportLog implements PublisherTransportInterface
{
    /**
     * Publishes a serialized message to the configured transport log sink.
     */
    public function publish(string $topic, array $payload): void
    {
        error_log('[outbox] topic='.$topic.' payload='.json_encode($payload));
    }
}
