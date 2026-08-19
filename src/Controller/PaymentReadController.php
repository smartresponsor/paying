<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\ControllerInterface\PaymentReadControllerInterface;
use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

/**
 * Returns read-side snapshots for individual payment aggregates.
 */
final readonly class PaymentReadController implements PaymentReadControllerInterface
{
    public function __construct(private PaymentRepositoryInterface $repo)
    {
    }

    #[PaymentRequireScopeAttribute(['payment:read'])]
    #[OA\Get(
        path: '/api/payments/{id}',
        summary: 'Read a payment aggregate by identifier.',
        tags: ['PaymentEntity'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payment read model.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', example: '01HZY9M8Q6M7X4YH3B2A1C0D9E'),
                        new OA\Property(property: 'orderId', type: 'string', example: 'order-1001'),
                        new OA\Property(property: 'status', type: 'string', example: 'processing'),
                        new OA\Property(property: 'amount', type: 'string', example: '50.00'),
                        new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                        new OA\Property(property: 'providerRef', type: 'string', example: 'internal-01HZY9M8Q6M7X4YH3B2A1C0D9E', nullable: true),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:read scope.'),
            new OA\Response(response: 404, description: 'Payment not found.'),
        ],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[Security(name: 'Bearer')]
    /**
     * Reads and serializes a single payment aggregate by identifier.
     */
    public function read(string $id): JsonResponse
    {
        if (!Ulid::isValid($id)) {
            return new JsonResponse(['error' => 'payment-not-found'], Response::HTTP_NOT_FOUND);
        }

        $payment = $this->repo->find($id);
        if (null === $payment) {
            return new JsonResponse(['error' => 'payment-not-found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->buildReadPayload($payment), Response::HTTP_OK);
    }

    /**
     * Shapes the read-side API payload for a payment aggregate.
     *
     * @return array{id: string, orderId: string, status: string, amount: string, currency: string, providerRef: ?string}
     */
    private function buildReadPayload(PaymentEntity $payment): array
    {
        return [
            'id' => $payment->slug(),
            'orderId' => $payment->orderId(),
            'status' => $payment->status()->value,
            'amount' => $payment->amount(),
            'currency' => $payment->currency(),
            'providerRef' => $payment->providerRef(),
        ];
    }
}
