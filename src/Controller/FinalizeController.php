<?php

// idempotent finalize controller

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
    #[OA\Post(path: '/payment/finalize/{id}')]
    #[Security(name: 'Bearer')]
    public function finalize(string $id, Request $request): JsonResponse
    {
        if (!Ulid::isValid($id)) {
            return $this->errorResponseFactory->paymentNotFound();
        }

        $existing = $this->repo->find($id);
        if (null === $existing) {
            return $this->errorResponseFactory->paymentNotFound();
        }

        if ($existing->isTerminal()) {
            return new JsonResponse([
                'id' => (string) $existing->id(),
                'status' => $existing->status()->value,
                'providerRef' => $existing->providerRef(),
            ], Response::HTTP_OK);
        }

        $data = $this->jsonBodyDecoder->decode($request, true);
        if (null === $data) {
            return $this->errorResponseFactory->badJsonBody();
        }

        $dto = new PaymentFinalizeRequestDto();
        $dto->provider = (string) ($data['provider'] ?? 'internal');
        $dto->providerRef = (string) ($data['providerRef'] ?? '');
        $dto->providerTransactionId = (string) ($data['providerTransactionId'] ?? '');
        $dto->status = (string) ($data['status'] ?? '');

        $validationResponse = $this->requestValidator->validate($dto);
        if (null !== $validationResponse) {
            return $validationResponse;
        }

        $payload = new PaymentFinalizePayload($dto->providerRef, $dto->providerTransactionId, $dto->status);

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
