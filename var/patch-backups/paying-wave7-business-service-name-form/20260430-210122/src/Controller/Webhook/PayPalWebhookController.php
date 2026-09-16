<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller\Webhook;

use App\Paying\Service\Webhook\JsonSchemaValidator;
use App\Paying\Service\Webhook\PayPalEventNormalizer;
use App\Paying\Service\Webhook\PayPalSignatureValidator;
use App\Paying\ServiceInterface\WebhookIngestServiceInterface;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles PayPal webhook callbacks and normalizes them into the payment runtime.
 */
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

    #[OA\Post(
        path: '/webhook/paypal',
        summary: 'Accept a PayPal webhook callback.',
        tags: ['PaymentEntity Webhooks'],
        responses: [
            new OA\Response(response: 200, description: 'Webhook accepted or recognized as duplicate.'),
            new OA\Response(response: 400, description: 'Invalid signature or malformed payload.'),
        ],
    )]
    /**
     * Verifies and processes an inbound PayPal webhook request.
     */
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
