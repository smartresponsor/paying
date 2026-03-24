<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentCreateStartFinalizeVerticalTest extends WebTestCase
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

    public function testCreateStartFinalizeReadAndRefundVertical(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/payments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'orderId' => 'vertical-order-1001',
                'amountMinor' => 1250,
                'currency' => 'USD',
            ]),
        );
        self::assertSame(201, $client->getResponse()->getStatusCode());
        $created = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($created);
        self::assertSame('new', $created['status'] ?? null);
        self::assertArrayHasKey('id', $created);
        $createdId = (string) $created['id'];

        $client->request('GET', '/api/payments/'.$createdId);
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $client->request(
            'POST',
            '/payment/start',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_Idempotency_Key' => 'vertical-start-1001'],
            (string) json_encode([
                'amount' => '12.50',
                'currency' => 'USD',
                'provider' => 'internal',
            ]),
        );
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $started = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($started);
        self::assertSame('processing', $started['status'] ?? null);
        self::assertArrayHasKey('payment', $started);
        $startedId = (string) $started['payment'];

        $client->request(
            'POST',
            '/payment/finalize/'.$startedId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'provider' => 'internal',
                'status' => 'completed',
            ]),
        );
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $finalized = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($finalized);
        self::assertSame('completed', $finalized['status'] ?? null);

        $client->request('GET', '/api/payments/'.$startedId);
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $read = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($read);
        self::assertSame('completed', $read['status'] ?? null);

        $client->request(
            'POST',
            '/api/payments/'.$startedId.'/refund',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'amount' => '12.50',
                'provider' => 'internal',
            ]),
        );
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $refunded = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($refunded);
        self::assertSame('refunded', $refunded['status'] ?? null);
    }
}
