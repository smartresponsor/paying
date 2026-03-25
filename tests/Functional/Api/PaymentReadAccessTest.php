<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentReadAccessTest extends WebTestCase
{
    public function testPaymentConsoleRequiresBearerToken(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');

        $client = static::createClient();
        $client->request('GET', '/payment/console');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testPaymentStatusRequiresBearerToken(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');

        $client = static::createClient();
        $client->request('GET', '/status');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }
}
