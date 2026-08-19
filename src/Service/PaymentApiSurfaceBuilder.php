<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\Dto\Payment\PaymentFinalizeRequestDto;
use App\Paying\Dto\Payment\PaymentRefundRequestDto;
use App\Paying\Dto\Payment\PaymentStartRequestDto;
use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\PaymentApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\PaymentApiRequestValidatorInterface;
use App\Paying\ServiceInterface\PaymentApiStartHandlerInterface;
use App\Paying\ServiceInterface\PaymentProviderGuardInterface;
use App\Paying\ServiceInterface\PaymentRefundServiceInterface;
use App\Paying\ServiceInterface\PaymentStartInput;
use App\Paying\ValueObject\PaymentFinalizePayload;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

final readonly class PaymentApiSurfaceBuilder
{
    public function __construct(
        private PaymentApiStartHandlerInterface $startHandler,
        private PaymentApiErrorResponseFactoryInterface $errorResponseFactory,
        private PaymentApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private PaymentApiRequestValidatorInterface $requestValidator,
        private PaymentProviderGuardInterface $guard,
        private PaymentRepositoryInterface $repo,
        private PaymentRefundServiceInterface $refundService,
        private LoggerInterface $logger,
    ) {
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    #[OA\Post(
        path: '/payment/start',
        summary: 'Create and start a payment execution flow.',
        tags: ['PaymentEntity'],
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
            required: ['orderId', 'amount', 'currency', 'provider'],
            properties: [
                new OA\Property(property: 'orderId', type: 'string', example: 'order-1001'),
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

        $dto = $this->hydrateStartRequestDto($data);
        $validationResponse = $this->requestValidator->validate($dto);
        if (null !== $validationResponse) {
            return $validationResponse;
        }

        $key = (string) $request->headers->get('Idempotency-Key', '');
        $payloadHash = hash('sha256', $request->getContent());
        $result = $this->startHandler->handle($this->buildStartInput($dto), $key, $payloadHash);

        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    #[OA\Post(
        path: '/payment/finalize/{id}',
        summary: 'Finalize a payment flow for an existing payment aggregate.',
        tags: ['PaymentEntity'],
        responses: [
            new OA\Response(response: 200, description: 'Payment finalized.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:write scope.'),
            new OA\Response(response: 404, description: 'Payment not found.'),
            new OA\Response(response: 422, description: 'Validation failed.'),
        ],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'provider', type: 'string', example: 'internal'),
                new OA\Property(property: 'providerRef', type: 'string', example: 'stripe_pi_123'),
                new OA\Property(property: 'gatewayTransactionId', type: 'string', example: 'txn_123'),
                new OA\Property(property: 'status', type: 'string', example: 'completed'),
            ],
            type: 'object',
        ),
    )]
    #[Security(name: 'Bearer')]
    public function finalize(string $id, Request $request): JsonResponse
    {
        if (!Ulid::isValid($id)) {
            return $this->errorResponseFactory->paymentNotFound();
        }

        $data = $this->jsonBodyDecoder->decode($request, true);
        if (null === $data) {
            return $this->errorResponseFactory->badJsonBody();
        }

        $dto = $this->hydrateFinalizeRequestDto($data, $request);
        $validationResponse = $this->requestValidator->validate($dto);
        if (null !== $validationResponse) {
            return $validationResponse;
        }

        $existing = $this->repo->find($id);
        if (null === $existing) {
            return $this->errorResponseFactory->paymentNotFound();
        }

        $payload = new PaymentFinalizePayload($dto->providerRef, $dto->gatewayTransactionId, $dto->status);
        $resolved = $this->guard->finalize($dto->provider, new Ulid($id), $payload->toProviderPayload());
        $existing->syncFrom($resolved);
        $this->repo->save($existing);

        return new JsonResponse($this->buildFinalizePayload($existing), Response::HTTP_OK);
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    #[OA\Post(
        path: '/api/payment/refund/{id}',
        summary: 'Refund an existing payment aggregate.',
        tags: ['PaymentEntity'],
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
     * @param array<string, mixed> $data
     */
    private function hydrateStartRequestDto(array $data): PaymentStartRequestDto
    {
        $dto = new PaymentStartRequestDto();
        $dto->orderId = (string) ($data['orderId'] ?? '');
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->currency = strtoupper((string) ($data['currency'] ?? 'USD'));
        $dto->provider = (string) ($data['provider'] ?? 'internal');

        return $dto;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrateFinalizeRequestDto(array $data, Request $request): PaymentFinalizeRequestDto
    {
        $dto = new PaymentFinalizeRequestDto();
        $dto->provider = (string) ($data['provider'] ?? $request->query->get('provider', 'internal'));
        $dto->providerRef = (string) ($data['providerRef'] ?? '');
        $dto->gatewayTransactionId = (string) ($data['gatewayTransactionId'] ?? '');
        $dto->status = (string) ($data['status'] ?? '');

        return $dto;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrateRefundRequestDto(array $data): PaymentRefundRequestDto
    {
        $dto = new PaymentRefundRequestDto();
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->provider = (string) ($data['provider'] ?? 'internal');

        return $dto;
    }

    private function buildStartInput(PaymentStartRequestDto $dto): PaymentStartInput
    {
        return new PaymentStartInput($dto->orderId, $dto->provider, $dto->amount, $dto->currency);
    }

    /**
     * @return array{id: string, status: string, providerRef: ?string}
     */
    private function buildFinalizePayload(PaymentEntity $payment): array
    {
        return [
            'id' => $payment->slug(),
            'status' => $payment->status()->value,
            'providerRef' => $payment->providerRef(),
        ];
    }

    /**
     * @return array{id: string, status: string, amount: string, currency: string, providerRef: ?string}
     */
    private function buildRefundPayload(PaymentEntity $payment): array
    {
        return [
            'id' => $payment->slug(),
            'status' => $payment->status()->value,
            'amount' => $payment->amount(),
            'currency' => $payment->currency(),
            'providerRef' => $payment->providerRef(),
        ];
    }
}
