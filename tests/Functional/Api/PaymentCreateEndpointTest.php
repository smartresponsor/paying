<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Exercises the payment create endpoint scenario within the payment api test surface.
 */
final class PaymentCreateEndpointTest extends WebTestCase
{
    private ?string $originalOidcDisabled = null;

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

    /**
     * Verifies that create payment requires bearer token.
     */
    public function testCreatePaymentRequiresBearerToken(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');

        $client = self::createClient();
        $client->request(
            'POST',
            '/api/payments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'orderId' => 'order-1001',
                'amountMinor' => 5000,
                'currency' => 'USD',
            ]),
        );

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    /**
     * Verifies that create payment returns created when scope guard is disabled for functional smoke.
     */
    public function testCreatePaymentReturnsCreatedWhenScopeGuardIsDisabledForFunctionalSmoke(): void
    {
        $this->originalOidcDisabled = $_ENV['OIDC_DISABLED'] ?? null;
        $_ENV['OIDC_DISABLED'] = '1';
        putenv('OIDC_DISABLED=1');

        $client = self::createClient();
        $client->request(
            'POST',
            '/api/payments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'orderId' => 'order-1002',
                'amountMinor' => 5000,
                'currency' => 'USD',
            ]),
        );

        self::assertSame(201, $client->getResponse()->getStatusCode());
    }
}
