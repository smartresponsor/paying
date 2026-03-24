<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Payment;

use App\Attribute\Payment\RequireScope;
use App\Controller\Payment\Dto\PaymentRefundRequestDto;
use App\Service\Payment\RefundServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PaymentRefundController implements PaymentRefundControllerInterface
{
    public function __construct(
        private readonly RefundServiceInterface $refundService,
        private readonly ValidatorInterface $validator,
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
            return new JsonResponse(['error' => 'payment-not-found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']]], JsonResponse::HTTP_BAD_REQUEST);
        }

        $dto = new PaymentRefundRequestDto();
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->provider = (string) ($data['provider'] ?? 'internal');

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => (string) $violation->getPropertyPath(),
                    'message' => (string) $violation->getMessage(),
                ];
            }

            return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $payment = $this->refundService->refund(new Ulid($id), $dto->amount, $dto->provider);
        } catch (\RuntimeException) {
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
