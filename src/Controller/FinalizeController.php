<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\Controller\Dto\PaymentFinalizeRequestDto;
use App\ControllerInterface\FinalizeControllerInterface;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\ApiErrorResponseFactoryInterface;
use App\ServiceInterface\ApiJsonBodyDecoderInterface;
use App\ServiceInterface\ApiRequestValidatorInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ValueObject\PaymentFinalizePayload;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

final readonly class FinalizeController implements FinalizeControllerInterface
{
    public function __construct(
        private ProviderGuardInterface $guard,
        private PaymentRepositoryInterface $repo,
        private ApiErrorResponseFactoryInterface $errorResponseFactory,
        private ApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private ApiRequestValidatorInterface $requestValidator,
    ) {
    }

    #[RequireScope(['payment:write'])]
    #[OA\Post(
        path: '/payment/finalize/{id}',
        summary: 'Finalize a payment flow for an existing payment aggregate.',
        tags: ['Payment'],
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

        $dto = new PaymentFinalizeRequestDto();
        $dto->provider = (string) ($data['provider'] ?? $request->query->get('provider', 'internal'));
        $dto->providerRef = (string) ($data['providerRef'] ?? '');
        $dto->gatewayTransactionId = (string) ($data['gatewayTransactionId'] ?? '');
        $dto->status = (string) ($data['status'] ?? '');

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

        return new JsonResponse([
            'id' => (string) $existing->id(),
            'status' => $existing->status()->value,
            'providerRef' => $existing->providerRef(),
        ], Response::HTTP_OK);
    }
}
