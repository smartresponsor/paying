<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface WebhookIngestServiceInterface
{
    /**
     * @param array<string, mixed> $normalized
     *
     * @return array{status: string, outboxId: string|null}
     */
    public function ingest(string $provider, string $externalId, array $normalized, string $routingKey): array;
}
