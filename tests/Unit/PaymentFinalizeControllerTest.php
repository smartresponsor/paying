<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Controller\PaymentFinalizeController;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentApiErrorResponseFactoryInterface;
use App\Paying\ServiceInterface\PaymentApiJsonBodyDecoderInterface;
use App\Paying\ServiceInterface\PaymentApiRequestValidatorInterface;
use App\Paying\ServiceInterface\PaymentProviderGuardInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exercises the finalize controller scenario within the payment unit test surface.
 */
final class PaymentFinalizeControllerTest extends TestCase
{
    private PaymentProviderGuardInterface&MockObject $guard;

    private PaymentRepositoryInterface&MockObject $repo;

    private PaymentApiErrorResponseFactoryInterface&MockObject $errorResponseFactory;

    private PaymentApiJsonBodyDecoderInterface&MockObject $jsonBodyDecoder;

    private PaymentApiRequestValidatorInterface&MockObject $requestValidator;

    private PaymentFinalizeController $controller;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->guard = $this->createMock(PaymentProviderGuardInterface::class);
        $this->repo = $this->createMock(PaymentRepositoryInterface::class);
        $this->errorResponseFactory = $this->createMock(PaymentApiErrorResponseFactoryInterface::class);
        $this->jsonBodyDecoder = $this->createMock(PaymentApiJsonBodyDecoderInterface::class);
        $this->requestValidator = $this->createMock(PaymentApiRequestValidatorInterface::class);

        $this->controller = new PaymentFinalizeController(
            $this->guard,
            $this->repo,
            $this->errorResponseFactory,
            $this->jsonBodyDecoder,
            $this->requestValidator,
        );
    }

    /**
     * Verifies that finalize returns not found for invalid ulid before decoding or validation.
     */
    public function testFinalizeReturnsNotFoundForInvalidUlidBeforeDecodingOrValidation(): void
    {
        $request = new Request();
        $notFoundResponse = new JsonResponse(['error' => 'PaymentEntity not found.'], Response::HTTP_NOT_FOUND);

        $this->errorResponseFactory
            ->expects(self::once())
            ->method('paymentNotFound')
            ->willReturn($notFoundResponse);

        $this->jsonBodyDecoder
            ->expects(self::never())
            ->method('decode');

        $this->requestValidator
            ->expects(self::never())
            ->method('validate');

        $this->repo
            ->expects(self::never())
            ->method('find');

        $this->guard
            ->expects(self::never())
            ->method('finalize');

        $response = $this->controller->finalize('invalid-id', $request);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame($notFoundResponse, $response);
    }
}
