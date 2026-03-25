<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Ui;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentConsolePageTest extends WebTestCase
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

    public function testConsolePageRendersBootstrapSmokeForms(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/payment/console');

        self::assertSame(200, $client->getResponse()->getStatusCode());
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Payment Console', $content);
        self::assertGreaterThanOrEqual(4, $crawler->filter('form')->count());
        self::assertStringContainsString('Create payment', $content);
        self::assertStringContainsString('Start payment', $content);
        self::assertStringContainsString('Finalize payment', $content);
        self::assertStringContainsString('Refund payment', $content);
        self::assertStringContainsString('Recent payments', $content);
    }
}
