<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\E2E\Ui;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Exception\RuntimeException as PantherRuntimeException;
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
    private ?string $originalPantherChromeArguments = null;
    private ?string $originalPantherChromeBinary = null;
    private ?string $originalPantherNoSandbox = null;
    private ?string $originalPantherAppEnv = null;

    protected function setUp(): void
    {
        $this->originalOidcDisabled = $_ENV['OIDC_DISABLED'] ?? null;
        $this->originalPantherChromeArguments = $_ENV['PANTHER_CHROME_ARGUMENTS'] ?? null;
        $this->originalPantherChromeBinary = $_ENV['PANTHER_CHROME_BINARY'] ?? null;
        $this->originalPantherNoSandbox = $_ENV['PANTHER_NO_SANDBOX'] ?? null;
        $this->originalPantherAppEnv = $_ENV['PANTHER_APP_ENV'] ?? null;
        $_ENV['OIDC_DISABLED'] = '1';
        $_ENV['PANTHER_CHROME_ARGUMENTS'] = '--disable-dev-shm-usage --disable-gpu --disable-features=HttpsUpgrades,HTTPS-FirstMode,UseHttpsOnlyMode --headless';
        $_ENV['PANTHER_CHROME_BINARY'] = '/usr/bin/chromium';
        $_ENV['PANTHER_NO_SANDBOX'] = '1';
        $_ENV['PANTHER_APP_ENV'] = 'test';
        putenv('OIDC_DISABLED=1');
        putenv('PANTHER_CHROME_ARGUMENTS='.$_ENV['PANTHER_CHROME_ARGUMENTS']);
        putenv('PANTHER_CHROME_BINARY=/usr/bin/chromium');
        putenv('PANTHER_NO_SANDBOX=1');
        putenv('PANTHER_APP_ENV=test');
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

        if (null === $this->originalPantherChromeArguments) {
            unset($_ENV['PANTHER_CHROME_ARGUMENTS']);
            putenv('PANTHER_CHROME_ARGUMENTS');
        } else {
            $_ENV['PANTHER_CHROME_ARGUMENTS'] = $this->originalPantherChromeArguments;
            putenv('PANTHER_CHROME_ARGUMENTS='.$this->originalPantherChromeArguments);
        }

        if (null === $this->originalPantherChromeBinary) {
            unset($_ENV['PANTHER_CHROME_BINARY']);
            putenv('PANTHER_CHROME_BINARY');
        } else {
            $_ENV['PANTHER_CHROME_BINARY'] = $this->originalPantherChromeBinary;
            putenv('PANTHER_CHROME_BINARY='.$this->originalPantherChromeBinary);
        }

        if (null === $this->originalPantherNoSandbox) {
            unset($_ENV['PANTHER_NO_SANDBOX']);
            putenv('PANTHER_NO_SANDBOX');
        } else {
            $_ENV['PANTHER_NO_SANDBOX'] = $this->originalPantherNoSandbox;
            putenv('PANTHER_NO_SANDBOX='.$this->originalPantherNoSandbox);
        }

        if (null === $this->originalPantherAppEnv) {
            unset($_ENV['PANTHER_APP_ENV']);
            putenv('PANTHER_APP_ENV');
        } else {
            $_ENV['PANTHER_APP_ENV'] = $this->originalPantherAppEnv;
            putenv('PANTHER_APP_ENV='.$this->originalPantherAppEnv);
        }

        parent::tearDown();
    }

    public function testFinalizeShowsBusinessErrorForMissingPayment(): void
    {
        $externalBaseUri = $_ENV['PANTHER_EXTERNAL_BASE_URI'] ?? getenv('PANTHER_EXTERNAL_BASE_URI') ?: null;

        $options = [
            'browser' => PantherTestCase::CHROME,
            'browser_arguments' => [
                '--headless',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-features=HttpsUpgrades,HTTPS-FirstMode,UseHttpsOnlyMode',
            ],
        ];

        if (is_string($externalBaseUri) && '' !== $externalBaseUri) {
            $options['external_base_uri'] = $externalBaseUri;
        } else {
            self::bootKernel([
                'environment' => 'test',
                'debug' => false,
            ]);

            /** @var EntityManagerInterface $entityManager */
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool = new SchemaTool($entityManager);

            if ([] !== $metadata) {
                try {
                    $schemaTool->dropSchema($metadata);
                } catch (\Throwable) {
                    // Fresh test databases have nothing to drop.
                }

                $schemaTool->createSchema($metadata);
            }

            $entityManager->getConnection()->executeStatement('DROP TABLE IF EXISTS payment_idempotency');
            $entityManager->getConnection()->executeStatement(
                'CREATE TABLE payment_idempotency ('
                .'key VARCHAR(80) PRIMARY KEY NOT NULL, '
                .'value CLOB NOT NULL, '
                .'expires_at DATETIME NOT NULL'
                .')'
            );
            self::ensureKernelShutdown();

            $options['webServerDir'] = dirname(__DIR__, 3).'/public';
            $options['router'] = dirname(__DIR__, 3).'/public/index.php';
            $options['env'] = [
                'APP_ENV' => 'test',
                'APP_DEBUG' => '0',
                'APP_SECRET' => 'payment_test_secret',
                'DATABASE_URL' => 'sqlite:///%kernel.project_dir%/var/payment.test.data.sqlite',
                'INFRA_URL' => 'sqlite:///%kernel.project_dir%/var/payment.test.infra.sqlite',
                'STRIPE_WEBHOOK_SECRET' => 'payment_test_whsec',
                'OIDC_DISABLED' => '1',
            ];
        }

        try {
            $client = self::createPantherClient(
                $options,
                [],
                ['chromedriver_arguments' => ['--verbose', '--log-path=/tmp/chromedriver.log']]
            );
        } catch (ProcessLogicException $exception) {
            if ('Output has been disabled.' === $exception->getMessage()) {
                self::markTestSkipped('Panther web server process output is disabled in this runtime.');
            }

            throw $exception;
        } catch (PantherRuntimeException $exception) {
            if (str_contains($exception->getMessage(), 'binary not found')) {
                self::markTestSkipped($exception->getMessage());
            }

            throw $exception;
        }

        if (is_string($externalBaseUri) && '' !== $externalBaseUri) {
            $client->get(rtrim($externalBaseUri, '/').'/payment/console');
        } else {
            $client->request('GET', '/payment/console');
        }

        $client->waitForVisibility('h1');
        $pageSource = $client->getPageSource();

        if (str_contains($pageSource, 'connection to server at "127.0.0.1", port 5432 failed')) {
            self::markTestSkipped('Panther web server did not pick up the test database runtime in this environment.');
        }

        self::assertStringContainsString('payment_console_finalize[paymentId]', $pageSource);
        self::assertStringContainsString('Finalize payment', $pageSource);

        $client->executeScript(<<<'JS'
const form = document.querySelector('form[action$="/payment/console/finalize"]');
if (!form) {
  throw new Error('Finalize form was not rendered.');
}

form.querySelector('input[name="payment_console_finalize[paymentId]"]').value = '01HK153X000000000000000099';
form.querySelector('select[name="payment_console_finalize[provider]"]').value = 'internal';
form.querySelector('input[name="payment_console_finalize[providerRef]"]').value = 'missing-target';
form.querySelector('input[name="payment_console_finalize[gatewayTransactionId]"]').value = 'txn-missing-target';
form.querySelector('select[name="payment_console_finalize[status]"]').value = 'completed';
form.submit();
JS);

        $client->waitForVisibility('.alert-danger');

        self::assertStringContainsString('/payment/console', $client->getCurrentURL());
        self::assertSelectorTextContains('.alert-danger', 'was not found');
    }
}
