<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Controller\FinalizeController;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\ApiErrorResponseFactoryInterface;
use App\ServiceInterface\ApiJsonBodyDecoderInterface;
use App\ServiceInterface\ApiRequestValidatorInterface;
use App\ServiceInterface\ProviderGuardInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exercises the finalize controller scenario within the payment unit test surface.
 */
final class FinalizeControllerTest extends TestCase
{
    private ProviderGuardInterface&MockObject $guard;

    private PaymentRepositoryInterface&MockObject $repo;

    private ApiErrorResponseFactoryInterface&MockObject $errorResponseFactory;

    private ApiJsonBodyDecoderInterface&MockObject $jsonBodyDecoder;

    private ApiRequestValidatorInterface&MockObject $requestValidator;

    private FinalizeController $controller;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->guard = $this->createMock(ProviderGuardInterface::class);
        $this->repo = $this->createMock(PaymentRepositoryInterface::class);
        $this->errorResponseFactory = $this->createMock(ApiErrorResponseFactoryInterface::class);
        $this->jsonBodyDecoder = $this->createMock(ApiJsonBodyDecoderInterface::class);
        $this->requestValidator = $this->createMock(ApiRequestValidatorInterface::class);

        $this->controller = new FinalizeController(
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
        $notFoundResponse = new JsonResponse(['error' => 'Payment not found.'], Response::HTTP_NOT_FOUND);

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
