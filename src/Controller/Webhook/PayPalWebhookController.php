<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Service\Webhook\JsonSchemaValidator;
use App\Service\Webhook\PayPalEventNormalizer;
use App\Service\Webhook\PayPalSignatureValidator;
use App\ServiceInterface\WebhookIngestServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PayPalWebhookController
{
    public function __construct(
        private PayPalSignatureValidator $validator,
        private PayPalEventNormalizer $normalizer,
        private JsonSchemaValidator $schema,
        private WebhookIngestServiceInterface $webhookIngestService,
        private LoggerInterface $paymentAuditLogger,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $headers = array_change_key_case($request->headers->all());
        if (!$this->validator->isValid($payload, $headers)) {
            return new JsonResponse(['error' => 'invalid-signature'], Response::HTTP_BAD_REQUEST);
        }
        $data = json_decode($payload, true) ?? [];
        if (!$this->schema->validate($data, ['id', 'event_type'])) {
            return new JsonResponse(['error' => 'invalid-payload'], Response::HTTP_BAD_REQUEST);
        }
        $externalId = (string) ($data['id'] ?? '');
        if ('' === $externalId) {
            return new JsonResponse(['error' => 'invalid-id'], Response::HTTP_BAD_REQUEST);
        }

        $normalized = $this->normalizer->normalize($data);
        $routingKey = $this->normalizer->routingKey($data);
        $ingested = $this->webhookIngestService->ingest('paypal', $externalId, $normalized, $routingKey);

        if ('duplicate' === $ingested['status']) {
            return new JsonResponse(['status' => 'duplicate'], Response::HTTP_OK);
        }

        $this->paymentAuditLogger->info('PayPal webhook accepted', [
            'id' => $externalId,
            'type' => $data['event_type'] ?? '',
            'paymentId' => $normalized['paymentId'] ?? null,
            'routingKey' => $routingKey,
        ]);

        return new JsonResponse(['status' => 'queued', 'outbox_id' => $ingested['outboxId']], Response::HTTP_OK);
    }
}
