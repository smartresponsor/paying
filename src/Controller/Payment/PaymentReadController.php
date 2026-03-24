<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Attribute\Payment\RequireScope;
use App\Repository\Payment\PaymentRepositoryInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Uid\Ulid;

final class PaymentReadController implements PaymentReadControllerInterface
{
    public function __construct(private readonly PaymentRepositoryInterface $repo)
    {
    }

    #[RequireScope(['payment:read'])]
    #[OA\Get(
        path: '/api/payments/{id}',
        summary: 'Read a payment aggregate by identifier.',
        tags: ['Payment'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payment read model.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', example: '01HZY9M8Q6M7X4YH3B2A1C0D9E'),
                        new OA\Property(property: 'status', type: 'string', example: 'processing'),
                        new OA\Property(property: 'amount', type: 'string', example: '50.00'),
                        new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                        new OA\Property(property: 'providerRef', type: 'string', nullable: true, example: 'internal-01HZY9M8Q6M7X4YH3B2A1C0D9E'),
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
    public function read(string $id): JsonResponse
    {
        if (!Ulid::isValid($id)) {
            return new JsonResponse(['error' => 'payment-not-found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $payment = $this->repo->find($id);
        if (null === $payment) {
            return new JsonResponse(['error' => 'payment-not-found'], JsonResponse::HTTP_NOT_FOUND);
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
