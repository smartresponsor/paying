<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\ApiErrorResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiErrorResponseFactoryTest extends TestCase
{
    public function testBadJsonBodyReturnsStablePayload(): void
    {
        $factory = new ApiErrorResponseFactory();

        $response = $factory->badJsonBody();
        $payload = json_decode((string) $response->getContent(), true);

        self::assertSame(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame([
            'errors' => [['field' => 'body', 'message' => 'Invalid JSON body.']],
        ], $payload);
    }

    public function testPaymentNotFoundReturnsStablePayload(): void
    {
        $factory = new ApiErrorResponseFactory();

        $response = $factory->paymentNotFound();
        $payload = json_decode((string) $response->getContent(), true);

        self::assertSame(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame(['error' => 'payment-not-found'], $payload);
    }
}
