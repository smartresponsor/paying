<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\Controller\Dto\PaymentFinalizeRequestDto;
use App\ControllerInterface\FinalizeControllerInterface;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\ValidationErrorMapperInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FinalizeController implements FinalizeControllerInterface
{
    public function __construct(
        private readonly ProviderGuardInterface $guard,
        private readonly PaymentRepositoryInterface $repo,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorMapperInterface $validationErrorMapper,
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
        $data = json_decode($request->getContent(), true);
        if (null === $data) {
            $data = [];
        }
        if (!is_array($data)) {
            return new JsonResponse(['errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']]], JsonResponse::HTTP_BAD_REQUEST);
        }

        $dto = new PaymentFinalizeRequestDto();
        $dto->provider = (string) ($data['provider'] ?? $request->query->get('provider', 'internal'));
        $dto->providerRef = (string) ($data['providerRef'] ?? '');
        $dto->gatewayTransactionId = (string) ($data['gatewayTransactionId'] ?? '');
        $dto->status = (string) ($data['status'] ?? '');

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['errors' => $this->validationErrorMapper->toArray($violations)], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $existing = $this->repo->find($id);
        if (null === $existing) {
            return new JsonResponse(['error' => 'payment-not-found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $payload = new PaymentFinalizePayload($dto->providerRef, $dto->gatewayTransactionId, $dto->status);

        $resolved = $this->guard->finalize($dto->provider, new Ulid($id), $payload->toProviderPayload());
        $existing->syncFrom($resolved);
        $this->repo->save($existing);

        return new JsonResponse([
            'id' => (string) $existing->id(),
            'status' => $existing->status()->value,
            'providerRef' => $existing->providerRef(),
        ], JsonResponse::HTTP_OK);
    }
}
