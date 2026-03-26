<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\Controller\Dto\PaymentCreateRequestDto;
use App\ControllerInterface\PaymentCreateControllerInterface;
use App\ServiceInterface\PaymentServiceInterface;
use App\ServiceInterface\ValidationErrorMapperInterface;
use App\ServiceInterface\ApiErrorResponseFactoryInterface;
use App\ServiceInterface\ApiJsonBodyDecoderInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PaymentCreateController implements PaymentCreateControllerInterface
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorMapperInterface $validationErrorMapper,
        private readonly ApiErrorResponseFactoryInterface $errorResponseFactory,
        private readonly ApiJsonBodyDecoderInterface $jsonBodyDecoder,
    ) {
    }

    #[RequireScope(['payment:write'])]
    #[OA\Post(
        path: '/api/payments',
        summary: 'Create a payment aggregate.',
        tags: ['Payment'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Payment created.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', example: '01HZY9M8Q6M7X4YH3B2A1C0D9E'),
                        new OA\Property(property: 'status', type: 'string', example: 'new'),
                        new OA\Property(property: 'amount', type: 'string', example: '50.00'),
                        new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:write scope.'),
            new OA\Response(response: 422, description: 'Validation failed.'),
        ],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['orderId', 'amountMinor', 'currency'],
            properties: [
                new OA\Property(property: 'orderId', type: 'string', example: 'order-1001'),
                new OA\Property(property: 'amountMinor', type: 'integer', example: 5000),
                new OA\Property(property: 'currency', type: 'string', example: 'USD'),
            ],
            type: 'object',
        ),
    )]
    #[Security(name: 'Bearer')]
    public function create(Request $request): JsonResponse
    {
        $data = $this->jsonBodyDecoder->decode($request);
        if (null === $data) {
            return $this->errorResponseFactory->badJsonBody();
        }

        $dto = new PaymentCreateRequestDto();
        $dto->orderId = (string) ($data['orderId'] ?? '');
        $dto->amountMinor = (int) ($data['amountMinor'] ?? 0);
        $dto->currency = strtoupper((string) ($data['currency'] ?? 'USD'));

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['errors' => $this->validationErrorMapper->toArray($violations)], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payment = $this->paymentService->create($dto->orderId, $dto->amountMinor, $dto->currency);

        return new JsonResponse([
            'id' => (string) $payment->id(),
            'status' => $payment->status()->value,
            'amount' => $payment->amount(),
            'currency' => $payment->currency(),
        ], JsonResponse::HTTP_CREATED);
    }
}
