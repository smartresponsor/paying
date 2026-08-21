<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Service\PaymentApiErrorResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exercises the api error response factory scenario within the payment unit test surface.
 */
final class PaymentApiErrorResponseFactoryTest extends TestCase
{
    /**
     * Verifies that bad json body returns stable payload.
     */
    public function testBadJsonBodyReturnsStablePayload(): void
    {
        $factory = new PaymentApiErrorResponseFactory();

        $response = $factory->badJsonBody();
        $payload = json_decode((string) $response->getContent(), true);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame([
            'errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']],
        ], $payload);
    }

    /**
     * Verifies that payment not found returns stable payload.
     */
    public function testPaymentNotFoundReturnsStablePayload(): void
    {
        $factory = new PaymentApiErrorResponseFactory();

        $response = $factory->paymentNotFound();
        $payload = json_decode((string) $response->getContent(), true);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame(['error' => 'payment-not-found'], $payload);
    }
}
