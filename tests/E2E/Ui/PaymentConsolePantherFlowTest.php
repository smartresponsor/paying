<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\E2E\Ui;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Process\Exception\LogicException as ProcessLogicException;

if (class_exists(PantherTestCase::class)) {
    abstract class PaymentConsolePantherFlowTestBase extends PantherTestCase
    {
    }
} else {
    abstract class PaymentConsolePantherFlowTestBase extends TestCase
    {
        protected static function createPantherClient(): never
        {
            self::markTestSkipped('symfony/panther is not installed in this environment.');
        }
    }
}

final class PaymentConsolePantherFlowTest extends PaymentConsolePantherFlowTestBase
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
        try {
            $client = self::createPantherClient();
        } catch (ProcessLogicException $exception) {
            if ('Output has been disabled.' === $exception->getMessage()) {
                self::markTestSkipped('Panther web server process output is disabled in this runtime.');
            }

            throw $exception;
        }

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
