<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Payment;

use App\Attribute\Payment\RequireScope;
use App\Controller\Payment\Dto\PaymentStartRequestDto;
use App\Entity\Payment\Payment;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\Service\Payment\IdempotencyService;
use App\Service\Payment\ProviderGuardInterface;
use App\ValueObject\Payment\PaymentStatus;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class StartController implements StartControllerInterface
{
    public function __construct(
        private readonly ProviderGuardInterface $guard,
        private readonly PaymentRepositoryInterface $repo,
        private readonly IdempotencyService $idem,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[RequireScope(['payment:write'])]
    #[OA\Post(
        path: '/payment/start',
        summary: 'Create and start a payment execution flow.',
        tags: ['Payment'],
        responses: [
            new OA\Response(response: 200, description: 'Payment started.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:write scope.'),
            new OA\Response(response: 422, description: 'Validation failed.'),
        ],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['amount', 'currency', 'provider'],
            properties: [
                new OA\Property(property: 'amount', type: 'string', example: '50.00'),
                new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                new OA\Property(property: 'provider', type: 'string', example: 'internal'),
            ],
            type: 'object',
        ),
    )]
    #[Security(name: 'Bearer')]
    public function start(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']]], JsonResponse::HTTP_BAD_REQUEST);
        }

        $dto = new PaymentStartRequestDto();
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->currency = strtoupper((string) ($data['currency'] ?? 'USD'));
        $dto->provider = (string) ($data['provider'] ?? 'internal');

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => (string) $violation->getPropertyPath(),
                    'message' => (string) $violation->getMessage(),
                ];
            }

            return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $key = (string) $request->headers->get('Idempotency-Key', '');
        $payloadHash = hash('sha256', $request->getContent());

        $result = $this->idem->execute($key, $payloadHash, function () use ($dto, $key): array {
            $payment = new Payment(new Ulid(), PaymentStatus::new, $dto->amount, $dto->currency);
            $this->repo->save($payment);

            $providerResult = $this->guard->start($dto->provider, $payment, [
                'idempotencyKey' => '' !== $key ? $key : (string) $payment->id(),
                'projectId' => (string) $payment->id(),
            ]);

            $providerRef = isset($providerResult['providerRef']) ? (string) $providerResult['providerRef'] : null;
            $payment->markProcessing($providerRef);
            $this->repo->save($payment);

            return [
                'payment' => (string) $payment->id(),
                'provider' => $dto->provider,
                'status' => $payment->status()->value,
                'providerRef' => $providerRef,
                'result' => $providerResult,
            ];
        });

        return new JsonResponse($result, JsonResponse::HTTP_OK);
    }
}
