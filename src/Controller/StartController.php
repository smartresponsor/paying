<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\Controller\Dto\PaymentStartRequestDto;
use App\ControllerInterface\StartControllerInterface;
use App\ServiceInterface\PaymentApiStartHandlerInterface;
use App\ServiceInterface\ValidationErrorMapperInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class StartController implements StartControllerInterface
{
    public function __construct(
        private readonly PaymentApiStartHandlerInterface $startHandler,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorMapperInterface $validationErrorMapper,
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
        $data = $this->jsonBodyDecoder->decode($request);
        if (null === $data) {
            return $this->errorResponseFactory->badJsonBody();
        }

        $dto = new PaymentStartRequestDto();
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->currency = strtoupper((string) ($data['currency'] ?? 'USD'));
        $dto->provider = (string) ($data['provider'] ?? 'internal');

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['errors' => $this->validationErrorMapper->toArray($violations)], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $key = (string) $request->headers->get('Idempotency-Key', '');
        $payloadHash = hash('sha256', $request->getContent());
        $result = $this->startHandler->handle($dto, $key, $payloadHash);

        return new JsonResponse($result, JsonResponse::HTTP_OK);
    }
}
