<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\Business\PaymentOutboxMessageEntity;
use App\Paying\Entity\Business\PaymentWebhookLogEntity;
use App\Paying\RepositoryInterface\PaymentWebhookRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Owns Doctrine access for payment webhook deduplication and durable ingest.
 */
final readonly class PaymentWebhookRepository implements PaymentWebhookRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function listRecent(int $limit = 50): array
    {
        $logs = $this->em->getRepository(PaymentWebhookLogEntity::class)->findBy([], ['receivedAt' => 'DESC'], max(1, $limit));

        return array_values($logs);
    }

    public function ingest(string $provider, string $externalId, array $normalized, string $routingKey): array
    {
        $repo = $this->em->getRepository(PaymentWebhookLogEntity::class);
        $existing = $repo->findOneBy(['provider' => $provider, 'externalEventId' => $externalId]);
        if ($existing instanceof PaymentWebhookLogEntity) {
            $existing->markDuplicate();
            $this->em->flush();

            return ['status' => 'duplicate', 'outboxId' => null];
        }

        $log = new PaymentWebhookLogEntity($provider, $externalId, $normalized);
        $outbox = new PaymentOutboxMessageEntity((new Ulid())->toRfc4122(), $routingKey, $normalized, $routingKey);
        $this->em->persist($log);
        $this->em->persist($outbox);
        $log->markProcessed();
        $this->em->flush();

        return ['status' => 'queued', 'outboxId' => $outbox->slug()];
    }
}
