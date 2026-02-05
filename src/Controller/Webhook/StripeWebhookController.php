<?php
namespace OrderComponent\Payment\Controller\Webhook;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Entity\Payment\PaymentOutboxMessage;
use OrderComponent\Payment\Entity\Payment\PaymentWebhookLog;
use OrderComponent\Payment\Service\Payment\Webhook\StripeSignatureValidator;
use OrderComponent\Payment\Service\Payment\Webhook\StripeEventNormalizer;
use OrderComponent\Payment\Service\Payment\Webhook\JsonSchemaValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class StripeWebhookController
{
    public function __construct(
        private EntityManagerInterface $em,
        private StripeSignatureValidator $validator,
        private StripeEventNormalizer $normalizer,
        private JsonSchemaValidator $schema,
        private LoggerInterface $paymentAuditLogger
    ) {}

    #[Route(path: '/webhook/stripe', name: 'payment_webhook_stripe', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $sig = $request->headers->get('Stripe-Signature');
        $payload = $request->getContent();
        if (!$this->validator->isValid($payload, $sig)) {
            return new JsonResponse(['error' => 'invalid-signature'], 400);
        }
        $data = json_decode($payload, true) ?? [];
        if (!$this->schema->validate($data, ['id','type'])) {
            return new JsonResponse(['error' => 'invalid-payload'], 400);
        }
        $externalId = (string)($data['id'] ?? '');
        if ($externalId === '') {
            return new JsonResponse(['error' => 'invalid-id'], 400);
        }

        // Idempotency check
        $repo = $this->em->getRepository(PaymentWebhookLog::class);
        $existing = $repo->findOneBy(['provider' => 'stripe', 'externalEventId' => $externalId]);
        if ($existing) {
            $existing->markDuplicate();
            $this->em->flush();
            return new JsonResponse(['status' => 'duplicate']);
        }

        $log = new PaymentWebhookLog('stripe', $externalId, $data);
        $this->em->persist($log);

        $routingKey = $this->normalizer->routingKey($data);
        // In real impl, convert to normalized payload with paymentId, orderId, etc.
        $outbox = new PaymentOutboxMessage(\Ramsey\Uuid\Uuid::uuid4()->toString(), $routingKey, $data, $routingKey);
        $this->em->persist($outbox);

        $log->markProcessed();
        $this->em->flush();

        $this->paymentAuditLogger->info('Stripe webhook accepted', ['id' => $externalId, 'type' => $data['type'] ?? '']);
        return new JsonResponse(['status' => 'queued', 'outbox_id' => $outbox->id()]);
    }
}
