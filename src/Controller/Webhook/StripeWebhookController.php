<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Webhook;

use App\Entity\Payment\PaymentOutboxMessage;
use App\Entity\Payment\PaymentWebhookLog;
use App\Service\Payment\Webhook\JsonSchemaValidator;
use App\Service\Payment\Webhook\StripeEventNormalizer;
use App\Service\Payment\Webhook\StripeSignatureValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

final class StripeWebhookController
{
    public function __construct(
        private EntityManagerInterface $em,
        private StripeSignatureValidator $validator,
        private StripeEventNormalizer $normalizer,
        private JsonSchemaValidator $schema,
        private LoggerInterface $paymentAuditLogger,
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

        $repo = $this->em->getRepository(PaymentWebhookLog::class);
        $existing = $repo->findOneBy(['provider' => 'stripe', 'externalEventId' => $externalId]);
        if ($existing) {
            $existing->markDuplicate();
            $this->em->flush();

            return new JsonResponse(['status' => 'duplicate'], JsonResponse::HTTP_OK);
        }

        $normalized = $this->normalizer->normalize($data);
        $routingKey = $this->normalizer->routingKey($data);
        $log = new PaymentWebhookLog('stripe', $externalId, $normalized);
        $this->em->persist($log);

        $outbox = new PaymentOutboxMessage((new Ulid())->toRfc4122(), $routingKey, $normalized, $routingKey);
        $this->em->persist($outbox);

        $log->markProcessed();
        $this->em->flush();

        $this->paymentAuditLogger->info('Stripe webhook accepted', [
            'id' => $externalId,
            'type' => $data['type'] ?? '',
            'paymentId' => $normalized['paymentId'] ?? null,
            'routingKey' => $routingKey,
        ]);

        return new JsonResponse(['status' => 'queued', 'outbox_id' => $outbox->id()], JsonResponse::HTTP_OK);
    }
}
