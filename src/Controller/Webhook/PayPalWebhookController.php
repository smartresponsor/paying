<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Entity\Payment\PaymentOutboxMessage;
use App\Entity\Payment\PaymentWebhookLog;
use App\Service\Payment\Webhook\JsonSchemaValidator;
use App\Service\Payment\Webhook\PayPalEventNormalizer;
use App\Service\Payment\Webhook\PayPalSignatureValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

final class PayPalWebhookController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PayPalSignatureValidator $validator,
        private PayPalEventNormalizer $normalizer,
        private JsonSchemaValidator $schema,
        private LoggerInterface $paymentAuditLogger,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $headers = array_change_key_case($request->headers->all(), CASE_LOWER);
        if (!$this->validator->isValid($payload, $headers)) {
            return new JsonResponse(['error' => 'invalid-signature'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $data = json_decode($payload, true) ?? [];
        if (!$this->schema->validate($data, ['id', 'event_type'])) {
            return new JsonResponse(['error' => 'invalid-payload'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $externalId = (string) ($data['id'] ?? '');
        if ('' === $externalId) {
            return new JsonResponse(['error' => 'invalid-id'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $repo = $this->em->getRepository(PaymentWebhookLog::class);
        $existing = $repo->findOneBy(['provider' => 'paypal', 'externalEventId' => $externalId]);
        if ($existing) {
            $existing->markDuplicate();
            $this->em->flush();

            return new JsonResponse(['status' => 'duplicate'], JsonResponse::HTTP_OK);
        }

        $normalized = $this->normalizer->normalize($data);
        $routingKey = $this->normalizer->routingKey($data);
        $log = new PaymentWebhookLog('paypal', $externalId, $normalized);
        $this->em->persist($log);

        $outbox = new PaymentOutboxMessage((new Ulid())->toRfc4122(), $routingKey, $normalized, $routingKey);
        $this->em->persist($outbox);

        $log->markProcessed();
        $this->em->flush();

        $this->paymentAuditLogger->info('PayPal webhook accepted', [
            'id' => $externalId,
            'type' => $data['event_type'] ?? '',
            'paymentId' => $normalized['paymentId'] ?? null,
            'routingKey' => $routingKey,
        ]);

        return new JsonResponse(['status' => 'queued', 'outbox_id' => $outbox->id()], JsonResponse::HTTP_OK);
    }
}
