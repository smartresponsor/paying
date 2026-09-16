<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\ControllerInterface\PaymentRefundControllerInterface;
use App\Paying\Dto\Payment\PaymentRefundRequestDto;
use App\Paying\Entity\PaymentEntity;
use App\Paying\Service\PaymentNotFoundException;
use App\Paying\ServiceInterface\PaymentApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\PaymentApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\PaymentApiRequestValidatorInterface;
use App\Paying\ServiceInterface\PaymentRefundServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

final readonly class PaymentRefundController implements PaymentRefundControllerInterface
{
    public function __construct(
        private PaymentRefundServiceInterface $refundService,
        private PaymentApiErrorResponseFactoryInterface $errorResponseFactory,
        private PaymentApiJsonBodyDecoderInterface $jsonBodyDecoder,
        private PaymentApiRequestValidatorInterface $requestValidator,
        private LoggerInterface $logger,
    ) {
    }

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
    private function hydrateRefundRequestDto(array $data): PaymentRefundRequestDto
    {
        $dto = new PaymentRefundRequestDto();
        $dto->amount = (string) ($data['amount'] ?? '0.00');
        $dto->provider = (string) ($data['provider'] ?? 'internal');

        return $dto;
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
