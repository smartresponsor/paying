<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\ControllerInterface\PaymentFinalizeControllerInterface;
use App\Paying\Dto\Payment\PaymentFinalizeRequestDto;
use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\PaymentApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\PaymentApiRequestValidatorInterface;
use App\Paying\ServiceInterface\PaymentProviderGuardInterface;
use App\Paying\ValueObject\PaymentFinalizePayload;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

final readonly class PaymentFinalizeController implements PaymentFinalizeControllerInterface
{
    public function __construct(
        private PaymentProviderGuardInterface $guard,
        private PaymentRepositoryInterface $repo,
        private PaymentApiErrorResponseFactoryInterface $errorResponseFactory,
        private PaymentApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private PaymentApiRequestValidatorInterface $requestValidator,
    ) {
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    #[OA\Post(
        path: '/payment/finalize/{id}',
        summary: 'Finalize a payment flow for an existing payment aggregate.',
        tags: ['PaymentEntity'],
        responses: [
            new OA\Response(response: 200, description: 'PaymentEntity finalized.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:write scope.'),
            new OA\Response(response: 404, description: 'PaymentEntity not found.'),
            new OA\Response(response: 422, description: 'Validation failed.'),
        ],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
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
}
