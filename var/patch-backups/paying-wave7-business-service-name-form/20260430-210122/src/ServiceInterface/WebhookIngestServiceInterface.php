<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the webhook ingest service interface payment service boundary.
 */
interface WebhookIngestServiceInterface
{
    /**
     * Executes the ingest operation for the current payment workflow.
     *
     * @param array<string, mixed> $normalized
     *
     * @return array{status: 'duplicate'|'queued', outboxId: string|null}
     */
    public function ingest(string $provider, string $externalId, array $normalized, string $routingKey): array;
}
