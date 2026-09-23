<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\RepositoryInterface\PaymentWebhookRepositoryInterface;
use App\Paying\ServiceInterface\PaymentWebhookIngestServiceInterface;

/**
 * Provides the webhook ingest service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentWebhookIngestService implements PaymentWebhookIngestServiceInterface
{
    public function __construct(private PaymentWebhookRepositoryInterface $webhooks)
    {
    }

    /**
     * Executes the ingest operation for the current payment workflow.
     *
     * @param array<string, mixed> $normalized
     *
     * @return array{status: 'duplicate'|'queued', outboxId: string|null}
     */
    public function ingest(string $provider, string $externalId, array $normalized, string $routingKey): array
    {
        return $this->webhooks->ingest($provider, $externalId, $normalized, $routingKey);
    }
}
