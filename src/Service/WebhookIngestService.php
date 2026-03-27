<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\PaymentOutboxMessage;
use App\Entity\PaymentWebhookLog;
use App\ServiceInterface\WebhookIngestServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class WebhookIngestService implements WebhookIngestServiceInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

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

        $outbox = new PaymentOutboxMessage((new Ulid())->toRfc4122(), $routingKey, $normalized, $routingKey);
        $this->em->persist($outbox);

        $log->markProcessed();
        $this->em->flush();

        return ['status' => 'queued', 'outboxId' => $outbox->id()];
    }
}
