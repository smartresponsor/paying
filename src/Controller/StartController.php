<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\Controller\Dto\PaymentStartRequestDto;
use App\ControllerInterface\StartControllerInterface;
use App\Service\IdempotencyService;
use App\ServiceInterface\PaymentStartServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class StartController implements StartControllerInterface
{
    public function __construct(
        private readonly PaymentStartServiceInterface $paymentStartService,
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
            $started = $this->paymentStartService->start($dto->provider, $dto->amount, $dto->currency, $key, 'api');
            $payment = $started['payment'];

            return [
                'payment' => (string) $payment->id(),
                'provider' => $dto->provider,
                'status' => $payment->status()->value,
                'providerRef' => $started['providerRef'],
                'result' => $started['result'],
            ];
        });

        return new JsonResponse($result, JsonResponse::HTTP_OK);
    }
}
