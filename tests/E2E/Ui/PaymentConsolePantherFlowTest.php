<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\E2E\Ui;

use Symfony\Component\Panther\PantherTestCase;

final class PaymentConsolePantherFlowTest extends PantherTestCase
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

    public function testFinalizeShowsBusinessErrorForMissingPayment(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/payment/console');

        $form = $crawler->selectButton('Finalize payment')->form([
            'payment_console_finalize[paymentId]' => '01HK153X000000000000000099',
            'payment_console_finalize[provider]' => 'internal',
            'payment_console_finalize[providerRef]' => 'missing-target',
            'payment_console_finalize[gatewayTransactionId]' => 'txn-missing-target',
            'payment_console_finalize[status]' => 'completed',
        ]);

        $client->submit($form);
        $client->waitForVisibility('.alert-danger');

        self::assertStringContainsString('/payment/console', $client->getCurrentURL());
        self::assertSelectorTextContains('.alert-danger', 'was not found');
    }
}
