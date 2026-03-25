<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\E2E\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StripeWebhookKernelFlowTest extends WebTestCase
{
    public function testEndToEndStripeWebhook(): void
    {
        $client = static::createClient();
        $payload = json_encode(['id' => 'evt_ker_1', 'type' => 'payment_intent.succeeded'], JSON_THROW_ON_ERROR);
        $timestamp = '1';
        $secret = (string) ($_ENV['STRIPE_WEBHOOK_SECRET'] ?? 'payment_test_whsec');
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        $client->request('POST', '/webhook/stripe', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Stripe-Signature' => sprintf('t=%s,v1=%s', $timestamp, $signature),
        ], $payload);

        $this->assertTrue($client->getResponse()->isSuccessful(), (string) $client->getResponse()->getContent());

        // NOTE: here you would run `payment:outbox:process` and then a messenger consumer in test env.
        $this->assertTrue(true);
    }
}
