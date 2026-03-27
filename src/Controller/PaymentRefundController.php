<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\Controller\Dto\PaymentRefundRequestDto;
use App\ControllerInterface\PaymentRefundControllerInterface;
use App\Service\PaymentNotFoundException;
use App\ServiceInterface\ApiErrorResponseFactoryInterface;
use App\ServiceInterface\ApiJsonBodyDecoderInterface;
use App\ServiceInterface\ApiRequestValidatorInterface;
use App\ServiceInterface\RefundServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

final class PaymentRefundController implements PaymentRefundControllerInterface
{
    public function __construct(
        private readonly RefundServiceInterface $refundService,
        private readonly ApiErrorResponseFactoryInterface $errorResponseFactory,
        private readonly ApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private readonly ApiRequestValidatorInterface $requestValidator,
        private readonly LoggerInterface $logger,
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
    public function refund(string $id, Request $request): JsonResponse
    {
        if (!Ulid::isValid($id)) {
            return $this->errorResponseFactory->paymentNotFound();
        }

        $data = $this->jsonBodyDecoder->decode($request);
        if (null === $data) {
            return $this->errorResponseFactory->badJsonBody();
        }

        $dto = new PaymentRefundRequestDto();
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->provider = (string) ($data['provider'] ?? 'internal');

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

        return new JsonResponse([
            'id' => (string) $payment->id(),
            'status' => $payment->status()->value,
            'amount' => $payment->amount(),
            'currency' => $payment->currency(),
            'providerRef' => $payment->providerRef(),
        ], JsonResponse::HTTP_OK);
    }
}
