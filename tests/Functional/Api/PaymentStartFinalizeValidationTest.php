<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentStartFinalizeValidationTest extends WebTestCase
{
    private ?string $originalOidcDisabled = null;

    protected function setUp(): void
    {
        $this->originalOidcDisabled = $_ENV['OIDC_DISABLED'] ?? null;
        $_ENV['OIDC_DISABLED'] = '1';
        putenv('OIDC_DISABLED=1');
    }

    protected function tearDown(): void
    {
        if (null === $this->originalOidcDisabled) {
            unset($_ENV['OIDC_DISABLED']);
            putenv('OIDC_DISABLED');
        } else {
            $_ENV['OIDC_DISABLED'] = $this->originalOidcDisabled;
            putenv('OIDC_DISABLED='.$this->originalOidcDisabled);
        }

        parent::tearDown();
    }

    public function testStartPaymentReturnsUnprocessableEntityForInvalidPayload(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/payment/start',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'amount' => '12',
                'currency' => 'USD',
                'provider' => 'internal',
            ]),
        );

        self::assertSame(422, $client->getResponse()->getStatusCode());
    }

    public function testFinalizePaymentReturnsUnprocessableEntityForUnknownProvider(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/payment/finalize/01HZY9M8Q6M7X4YH3B2A1C0D9E',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'provider' => 'paypal',
            ]),
        );

        self::assertSame(422, $client->getResponse()->getStatusCode());
    }
}
