<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Confirms the operational payment routes expose the intended security boundary.
 */
final class PaymentOperationalAccessTest extends WebTestCase
{
    public function testStatusRequiresBearerToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/status');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testMetricsRequiresBearerToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/metrics');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testDlqListRequiresBearerToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/payment/dlq');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testDlqReplayRequiresBearerToken(): void
    {
        $client = static::createClient();
        $client->request('POST', '/payment/dlq/replay/1');

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testApiDocsJsonRemainsPublic(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc/json');

        self::assertNotContains($client->getResponse()->getStatusCode(), [401, 403]);
    }

    public function testApiDocsHtmlRemainsPublic(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc');

        self::assertNotContains($client->getResponse()->getStatusCode(), [401, 403]);
    }

    public function testDedicatedWebhookRouteRemainsPublicButSignatureProtected(): void
    {
        $client = static::createClient();
        $client->request('POST', '/webhook/stripe', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: '{"type":"payment_intent.succeeded"}');

        self::assertContains($client->getResponse()->getStatusCode(), [400, 422]);
    }

    public function testPayPalWebhookRouteRemainsPublicButSignatureProtected(): void
    {
        $client = static::createClient();
        $client->request('POST', '/webhook/paypal', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: '{"event_type":"PAYMENT.CAPTURE.COMPLETED"}');

        self::assertContains($client->getResponse()->getStatusCode(), [400, 422]);
    }

    public function testGenericWebhookRouteRemainsPublicButVerificationProtected(): void
    {
        $client = static::createClient();
        $client->request('POST', '/payment/webhook/stripe', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: '{"type":"payment_intent.succeeded"}');

        self::assertContains($client->getResponse()->getStatusCode(), [400, 422]);
    }
}
