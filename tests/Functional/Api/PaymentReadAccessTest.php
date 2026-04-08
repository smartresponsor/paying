<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Exercises the payment read access scenario within the payment api test surface.
 */
final class PaymentReadAccessTest extends WebTestCase
{
    /**
     * Verifies that payment console requires bearer token.
     */
    public function testPaymentConsoleRequiresBearerToken(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');

        $client = self::createClient();
        $client->request('GET', '/payment/console');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    /**
     * Verifies that payment status requires bearer token.
     */
    public function testPaymentStatusRequiresBearerToken(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');

        $client = self::createClient();
        $client->request('GET', '/status');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }
}
