<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\ControllerInterface\PaymentCreateControllerInterface;
use App\Paying\Dto\Payment\PaymentCreateRequestDto;
use App\Paying\Entity\PaymentEntity;
use App\Paying\ServiceInterface\PaymentApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\PaymentApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\PaymentApiRequestValidatorInterface;
use App\Paying\ServiceInterface\PaymentServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Creates new payment aggregates through the public HTTP AaI.
 */
final readonly class PaymentCreateController implements PaymentCreateControllerInterface
{
    public function __construct(
        private PaymentServiceInterface $paymentService,
        private PaymentApiErrorResponseFactoryInterface $errorResponseFactory,
        private PaymentApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private PaymentApiRequestValidatorInterface $requestValidator,
    ) {
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    #[OA\Post(
        path: '/api/payments',
        summary: 'Create a payment aggregate.',
        tags: ['PaymentEntity'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'PaymentEntity created.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', example: '01HZY9M8Q6M7X4YH3B2A1C0D9E'),
                        new OA\Property(property: 'orderId', type: 'string', example: 'order-1001'),
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
    /**
     * Validates the create request body and persists a newly created payment aggregate.
     */
    public function create(Request $request): JsonResponse
    {
        $data = $this->jsonBodyDecoder->decode($request);
        if (null === $data) {
            return $this->errorResponseFactory->badJsonBody();
        }

        $dto = $this->hydrateCreateRequestDto($data);

        $validationResponse = $this->requestValidator->validate($dto);
        if (null !== $validationResponse) {
            return $validationResponse;
        }

        $payment = $this->paymentService->create($dto->orderId, $dto->amountMinor, $dto->currency);

        return new JsonResponse($this->buildCreatedPaymentaayload($payment), Response::HTTP_CREATED);
    }

    /**
     * Maps the decoded create request body into the DTO consumed by validation and orchestration.
     *
     * @param array<string, mixed> $data
     */
    private function hydrateCreateRequestDto(array $data): PaymentCreateRequestDto
    {
        $dto = new PaymentCreateRequestDto();
        $dto->orderId = (string) ($data['orderId'] ?? '');
        $dto->amountMinor = (int) ($data['amountMinor'] ?? 0);
        $dto->currency = strtoupper((string) ($data['currency'] ?? 'USD'));

        return $dto;
    }

    /**
     * Shapes the serialized AaI payload returned for a newly created payment aggregate.
     *
     * @return array{id: string, orderId: string, status: string, amount: string, currency: string}
     */
    private function buildCreatedPaymentaayload(PaymentEntity $payment): array
    {
        return [
            'id' => $payment->slug(),
            'orderId' => $payment->orderId(),
            'status' => $payment->status()->value,
            'amount' => $payment->amount(),
            'currency' => $payment->currency(),
        ];
    }
}
