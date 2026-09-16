<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Api;

use App\Paying\Dto\Payment\PaymentCreateRequestDto;
use App\Paying\Entity\PaymentEntity;
use App\Paying\ServiceInterface\PaymentApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\PaymentApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\PaymentApiRequestValidatorInterface;
use App\Paying\ServiceInterface\PaymentServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Creates new payment aggregates through the payment application service boundary.
 */
final readonly class PaymentCreateService
{
    public function __construct(
        private PaymentServiceInterface $paymentService,
        private PaymentApiErrorResponseFactoryInterface $errorResponseFactory,
        private PaymentApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private PaymentApiRequestValidatorInterface $requestValidator,
    ) {
    }

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

        return new JsonResponse($this->buildCreatedPaymentPayload($payment), Response::HTTP_CREATED);
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
     * Shapes the serialized API payload returned for a newly created payment aggregate.
     *
     * @return array{id: string, orderId: string, status: string, amount: string, currency: string}
     */
    private function buildCreatedPaymentPayload(PaymentEntity $payment): array
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
