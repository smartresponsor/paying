<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\RepositoryInterface;

use App\Paying\Entity\Business\PaymentWebhookLogEntity;

/**
 * Defines payment webhook log and ingest persistence operations.
 */
interface PaymentWebhookRepositoryInterface
{
    /** @return list<PaymentWebhookLogEntity> */
    public function listRecent(int $limit = 50): array;

    /**
     * @param array<string, mixed> $normalized
     *
     * @return array{status: 'duplicate'|'queued', outboxId: string|null}
     */
    public function ingest(string $provider, string $externalId, array $normalized, string $routingKey): array;
}
