<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller\Webhook;

use App\Paying\Service\Webhook\JsonSchemaValidator;
use App\Paying\Service\Webhook\StripeEventNormalizer;
use App\Paying\Service\Webhook\StripeSignatureValidator;
use App\Paying\ServiceInterface\WebhookIngestServiceInterface;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles Stripe webhook callbacks and normalizes them into the payment runtime.
 */
final readonly class StripeWebhookController
{
    public function __construct(
        private StripeSignatureValidator $validator,
        private StripeEventNormalizer $normalizer,
        private JsonSchemaValidator $schema,
        private WebhookIngestServiceInterface $webhookIngestService,
        private LoggerInterface $paymentAuditLogger,
    ) {
    }

    #[OA\Post(
        path: '/webhook/stripe',
        summary: 'Accept a Stripe webhook callback.',
        tags: ['PaymentEntity Webhooks'],
        responses: [
            new OA\Response(response: 200, description: 'Webhook accepted or recognized as duplicate.'),
            new OA\Response(response: 400, description: 'Invalid signature or malformed payload.'),
        ],
    )]
    /**
     * Verifies and processes an inbound Stripe webhook request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $sig = $request->headers->get('Stripe-Signature');
        $payload = $request->getContent();
        if (!$this->validator->isValid($payload, $sig)) {
            return new JsonResponse(['error' => 'invalid-signature'], Response::HTTP_BAD_REQUEST);
        }
        $data = json_decode($payload, true) ?? [];
        if (!$this->schema->validate($data, ['id', 'type'])) {
            return new JsonResponse(['error' => 'invalid-payload'], Response::HTTP_BAD_REQUEST);
        }
        $externalId = (string) ($data['id'] ?? '');
        if ('' === $externalId) {
            return new JsonResponse(['error' => 'invalid-id'], Response::HTTP_BAD_REQUEST);
        }

        $normalized = $this->normalizer->normalize($data);
        $routingKey = $this->normalizer->routingKey($data);
        $ingested = $this->webhookIngestService->ingest('stripe', $externalId, $normalized, $routingKey);

        if ('duplicate' === $ingested['status']) {
            return new JsonResponse(['status' => 'duplicate'], Response::HTTP_OK);
        }

        $this->paymentAuditLogger->info('Stripe webhook accepted', [
            'id' => $externalId,
            'type' => $data['type'] ?? '',
            'paymentId' => $normalized['paymentId'] ?? null,
            'routingKey' => $routingKey,
        ]);

        return new JsonResponse(['status' => 'queued', 'outbox_id' => $ingested['outboxId']], Response::HTTP_OK);
    }
}
