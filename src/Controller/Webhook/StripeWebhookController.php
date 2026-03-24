<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Service\Payment\Webhook\JsonSchemaValidator;
use App\Service\Payment\Webhook\StripeEventNormalizer;
use App\Service\Payment\Webhook\StripeSignatureValidator;
use App\Service\Payment\WebhookIngestServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class StripeWebhookController
{
    public function __construct(
        private readonly StripeSignatureValidator $validator,
        private readonly StripeEventNormalizer $normalizer,
        private readonly JsonSchemaValidator $schema,
        private readonly WebhookIngestServiceInterface $webhookIngestService,
        private readonly LoggerInterface $paymentAuditLogger,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $sig = $request->headers->get('Stripe-Signature');
        $payload = $request->getContent();
        if (!$this->validator->isValid($payload, $sig)) {
            return new JsonResponse(['error' => 'invalid-signature'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $data = json_decode($payload, true) ?? [];
        if (!$this->schema->validate($data, ['id', 'type'])) {
            return new JsonResponse(['error' => 'invalid-payload'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $externalId = (string) ($data['id'] ?? '');
        if ('' === $externalId) {
            return new JsonResponse(['error' => 'invalid-id'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $normalized = $this->normalizer->normalize($data);
        $routingKey = $this->normalizer->routingKey($data);
        $ingested = $this->webhookIngestService->ingest('stripe', $externalId, $normalized, $routingKey);

        if ('duplicate' === $ingested['status']) {
            return new JsonResponse(['status' => 'duplicate'], JsonResponse::HTTP_OK);
        }

        $this->paymentAuditLogger->info('Stripe webhook accepted', [
            'id' => $externalId,
            'type' => $data['type'] ?? '',
            'paymentId' => $normalized['paymentId'] ?? null,
            'routingKey' => $routingKey,
        ]);

        return new JsonResponse(['status' => 'queued', 'outbox_id' => $ingested['outboxId']], JsonResponse::HTTP_OK);
    }
}
