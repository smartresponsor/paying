<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\Entity\Payment;

use App\Paying\Attribute\RequireScope;
use App\Paying\Controller\Dto\PaymentRefundRequestDto;
use App\Paying\ControllerInterface\PaymentRefundControllerInterface;
use App\Paying\Service\PaymentNotFoundException;
use App\Paying\ServiceInterface\ApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\ApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\ApiRequestValidatorInterface;
use App\Paying\ServiceInterface\RefundServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

/**
 * Accepts refund requests for existing payment aggregates through the HTTP API.
 */
final readonly class PaymentRefundController implements PaymentRefundControllerInterface
{
    public function __construct(
        private RefundServiceInterface $refundService,
        private ApiErrorResponseFactoryInterface $errorResponseFactory,
        private ApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private ApiRequestValidatorInterface $requestValidator,
        private LoggerInterface $logger,
    ) {
    }

    #[RequireScope(['payment:write'])]
    #[OA\Post(
        path: '/api/payments/{id}/refund',
        summary: 'Refund an existing payment aggregate.',
        tags: ['Payment'],
        responses: [
            new OA\Response(response: 200, description: 'Payment refunded.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:write scope.'),
            new OA\Response(response: 404, description: 'Payment not found.'),
            new OA\Response(response: 422, description: 'Validation failed.'),
        ],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['amount', 'provider'],
            properties: [
                new OA\Property(property: 'amount', type: 'string', example: '50.00'),
                new OA\Property(property: 'provider', type: 'string', example: 'internal'),
            ],
            type: 'object',
        ),
    )]
    #[Security(name: 'Bearer')]
    /**
     * Validates the refund request and applies the provider-specific refund transition.
     */
    public function refund(string $id, Request $request): JsonResponse
    {
        if (!Ulid::isValid($id)) {
            return $this->errorResponseFactory->paymentNotFound();
        }

        $data = $this->jsonBodyDecoder->decode($request);
        if (null === $data) {
            return $this->errorResponseFactory->badJsonBody();
        }

        $dto = $this->hydrateRefundRequestDto($data);

        $validationResponse = $this->requestValidator->validate($dto);
        if (null !== $validationResponse) {
            return $validationResponse;
        }

        try {
            $payment = $this->refundService->refund(new Ulid($id), $dto->amount, $dto->provider);
        } catch (PaymentNotFoundException $exception) {
            $this->logger->warning('Unable to refund payment.', [
                'payment_id' => $id,
                'error' => $exception->getMessage(),
            ]);

            return $this->errorResponseFactory->paymentNotFound();
        }

        return new JsonResponse($this->buildRefundPayload($payment), Response::HTTP_OK);
    }

    /**
     * Maps the decoded refund request body into the DTO consumed by validation and orchestration.
     *
     * @param array<string, mixed> $data
     */
    private function hydrateRefundRequestDto(array $data): PaymentRefundRequestDto
    {
        $dto = new PaymentRefundRequestDto();
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->provider = (string) ($data['provider'] ?? 'internal');

        return $dto;
    }

    /**
     * Shapes the serialized refund payload returned after a successful refund transition.
     *
     * @return array{id: string, status: string, amount: string, currency: string, providerRef: ?string}
     */
    private function buildRefundPayload(Payment $payment): array
    {
        return [
            'id' => (string) $payment->id(),
            'status' => $payment->status()->value,
            'amount' => $payment->amount(),
            'currency' => $payment->currency(),
            'providerRef' => $payment->providerRef(),
        ];
    }
}
