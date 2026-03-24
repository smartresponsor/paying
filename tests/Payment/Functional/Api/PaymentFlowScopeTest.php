<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentFlowScopeTest extends WebTestCase
{
    protected function setUp(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');
    }

    public function testStartPaymentRequiresBearerToken(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/payment/start',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'amount' => '50.00',
                'currency' => 'USD',
                'provider' => 'internal',
            ]),
        );

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testFinalizePaymentRequiresBearerToken(): void
    {
        $client = static::createClient();
        $client->request('POST', '/payment/finalize/01HZY9M8Q6M7X4YH3B2A1C0D9E');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }
}
