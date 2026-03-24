<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

    public function testCreatePaymentRequiresBearerToken(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');

        $client = static::createClient();
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

    public function testCreatePaymentReturnsCreatedWhenScopeGuardIsDisabledForFunctionalSmoke(): void
    {
        $this->originalOidcDisabled = $_ENV['OIDC_DISABLED'] ?? null;
        $_ENV['OIDC_DISABLED'] = '1';
        putenv('OIDC_DISABLED=1');

        $client = static::createClient();
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
