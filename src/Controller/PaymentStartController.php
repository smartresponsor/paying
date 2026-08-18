<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\ControllerInterface\PaymentStartControllerInterface;
use App\Paying\Dto\Payment\PaymentStartRequestDto;
use App\Paying\ServiceInterface\PaymentApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\PaymentApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\PaymentApiRequestValidatorInterface;
use App\Paying\ServiceInterface\PaymentApiStartHandlerInterface;
use App\Paying\ServiceInterface\PaymentStartInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PaymentStartController implements PaymentStartControllerInterface
{
    public function __construct(
        private PaymentApiStartHandlerInterface $startHandler,
        private PaymentApiErrorResponseFactoryInterface $errorResponseFactory,
        private PaymentApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private PaymentApiRequestValidatorInterface $requestValidator,
    ) {
    }

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

    private function buildStartInput(PaymentStartRequestDto $dto): PaymentStartInput
    {
        return new PaymentStartInput($dto->orderId, $dto->provider, $dto->amount, $dto->currency);
    }
}
