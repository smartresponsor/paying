<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Ui;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Exercises the payment console access smoke scenario within the payment ui test surface.
 */
final class PaymentConsoleAccessSmokeTest extends WebTestCase
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

    /**
     * Verifies that payment console remains reachable when oidc is disabled for smoke.
     */
    public function testPaymentConsoleRemainsReachableWhenOidcIsDisabledForSmoke(): void
    {
        $client = self::createClient();
        $client->request('GET', '/payment/console');

        self::assertSame(200, $client->getResponse()->getStatusCode());
    }
}
