<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentOutboxMessage;
use App\Paying\Entity\PaymentWebhookLog;
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
        $repo = $this->em->getRepository(PaymentWebhookLog::class);
        $existing = $repo->findOneBy(['provider' => $provider, 'externalEventId' => $externalId]);
        if ($existing) {
            $existing->markDuplicate();
            $this->em->flush();

            return ['status' => 'duplicate', 'outboxId' => null];
        }

        $log = new PaymentWebhookLog($provider, $externalId, $normalized);
        $this->em->persist($log);

        $outbox = new PaymentOutboxMessage(new Ulid()->toRfc4122(), $routingKey, $normalized, $routingKey);
        $this->em->persist($outbox);

        $log->markProcessed();
        $this->em->flush();

        return ['status' => 'queued', 'outboxId' => $outbox->id()];
    }
}
