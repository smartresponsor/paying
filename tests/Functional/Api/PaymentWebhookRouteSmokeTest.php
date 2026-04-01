<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentWebhookRouteSmokeTest extends WebTestCase
{
    protected function setUp(): void
    {
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');
    }

    public function testGenericWebhookRouteIsWiredToControllerChain(): void
    {
        $client = self::createClient();
        $client->request('POST', '/payment/webhook/stripe', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{}');

        self::assertSame(400, $client->getResponse()->getStatusCode());
    }
}
