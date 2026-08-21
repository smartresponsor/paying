<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentOutboxMessageEntity;
use App\Paying\Entity\PaymentWebhookLogEntity;
use App\Paying\ServiceInterface\WebhookIngestServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the webhook ingest service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class WebhookIngestService implements WebhookIngestServiceInterface
{
    public function __construct(private EntityManagerInterface $em)
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
        $repo = $this->em->getRepository(PaymentWebhookLogEntity::class);
        $existing = $repo->findOneBy(['provider' => $provider, 'externalEventId' => $externalId]);
        if ($existing) {
            $existing->markDuplicate();
            $this->em->flush();

            return ['status' => 'duplicate', 'outboxId' => null];
        }

        $log = new PaymentWebhookLogEntity($provider, $externalId, $normalized);
        $this->em->persist($log);

        $outbox = new PaymentOutboxMessageEntity(new Ulid()->toRfc4122(), $routingKey, $normalized, $routingKey);
        $this->em->persist($outbox);

        $log->markProcessed();
        $this->em->flush();

        return ['status' => 'queued', 'outboxId' => $outbox->id()];
    }
}
