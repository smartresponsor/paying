<?php
namespace OrderComponent\Payment\Controller\Webhook;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Entity\Payment\PaymentOutboxMessage;
use OrderComponent\Payment\Entity\Payment\PaymentWebhookLog;
use OrderComponent\Payment\Service\Payment\Webhook\PayPalSignatureValidator;
use OrderComponent\Payment\Service\Payment\Webhook\PayPalEventNormalizer;
use OrderComponent\Payment\Service\Payment\Webhook\JsonSchemaValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class PayPalWebhookController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PayPalSignatureValidator $validator,
        private PayPalEventNormalizer $normalizer,
        private JsonSchemaValidator $schema,
        private LoggerInterface $paymentAuditLogger
    ) {}

    #[Route(path: '/webhook/paypal', name: 'payment_webhook_paypal', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $headers = array_change_key_case($request->headers->all(), CASE_LOWER);
        if (!$this->validator->isValid($payload, $headers)) {
            return new JsonResponse(['error' => 'invalid-signature'], 400);
        }
        $data = json_decode($payload, true) ?? [];
        if (!$this->schema->validate($data, ['id','event_type'])) {
            return new JsonResponse(['error' => 'invalid-payload'], 400);
        }
        $externalId = (string)($data['id'] ?? '');
        if ($externalId === '') {
            return new JsonResponse(['error' => 'invalid-id'], 400);
        }

        $repo = $this->em->getRepository(PaymentWebhookLog::class);
        $existing = $repo->findOneBy(['provider' => 'paypal', 'externalEventId' => $externalId]);
        if ($existing) {
            $existing->markDuplicate();
            $this->em->flush();
            return new JsonResponse(['status' => 'duplicate']);
        }

        $log = new PaymentWebhookLog('paypal', $externalId, $data);
        $this->em->persist($log);

        $routingKey = $this->normalizer->routingKey($data);
        $outbox = new PaymentOutboxMessage(\Ramsey\Uuid\Uuid::uuid4()->toString(), $routingKey, $data, $routingKey);
        $this->em->persist($outbox);

        $log->markProcessed();
        $this->em->flush();

        $this->paymentAuditLogger->info('PayPal webhook accepted', ['id' => $externalId, 'type' => $data['event_type'] ?? '']);
        return new JsonResponse(['status' => 'queued', 'outbox_id' => $outbox->id()]);
    }
}
