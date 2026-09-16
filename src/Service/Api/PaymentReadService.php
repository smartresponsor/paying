<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Api;

use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

/**
 * Returns read-side snapshots for individual payment aggregates through the service layer.
 */
final readonly class PaymentReadService
{
    public function __construct(private PaymentRepositoryInterface $repo)
    {
    }

    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
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
