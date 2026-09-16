<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Service\PaymentTokenVerifier;
use App\Paying\ServiceInterface\PaymentOidcJwksCacheInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the token verifier hardening paths within the payment unit test surface.
 */
final class PaymentTokenVerifierTest extends TestCase
{
    public function testVerifyRejectsMalformedBase64UrlSegments(): void
    {
        $jwks = $this->createMock(PaymentOidcJwksCacheInterface::class);
        $jwks->expects(self::never())->method('get');

        $verifier = new PaymentTokenVerifier($jwks);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('jwt-segment-base64url');

        $verifier->verify('@@@@.@@@@.@@@@');
    }

    public function testVerifyRejectsUnsupportedJwkUseBeforeSignatureValidation(): void
    {
        $jwks = $this->createMock(PaymentOidcJwksCacheInterface::class);
        $jwks->expects(self::once())
            ->method('get')
            ->willReturn([
                'keys' => [[
                    'kid' => 'kid-1',
                    'kty' => 'RSA',
                    'use' => 'enc',
                    'alg' => 'RS256',
                    'n' => 'AQAB',
                    'e' => 'AQAB',
                ]],
            ]);

        $header = rtrim(strtr(base64_encode((string) json_encode(['alg' => 'RS256', 'kid' => 'kid-1'], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode((string) json_encode(['sub' => 'payment-user'], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $signature = rtrim(strtr(base64_encode('signature'), '+/', '-_'), '=');

        $verifier = new PaymentTokenVerifier($jwks);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('jwk-use-not-supported');

        $verifier->verify($header.'.'.$payload.'.'.$signature);
    }
}
