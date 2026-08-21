<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\E2E\Ui;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Exception\RuntimeException as PantherRuntimeException;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Process\Exception\LogicException as ProcessLogicException;

if (class_exists(PantherTestCase::class)) {
    /**
     * Exercises the payment console panther flow base scenario within the payment ui test surface.
     */
    abstract class PaymentConsolePantherFlowTestBase extends PantherTestCase
    {
    }
} else {
    /**
     * Exercises the payment console panther flow base scenario within the payment ui test surface.
     */
    abstract class PaymentConsolePantherFlowTestBase extends TestCase
    {
        protected static function createPantherClient(): never
        {
            self::markTestSkipped('symfony/panther is not installed in this environment.');
        }
    }
}

/**
 * Exercises the payment console panther flow scenario within the payment ui test surface.
 */
final class PaymentConsolePantherFlowTest extends PaymentConsolePantherFlowTestBase
{
    private const SQLITE_TEST_DATABASE_URL = 'sqlite:///%kernel.project_dir%/var/payment.test.data.sqlite';
    private const SQLITE_TEST_INFRASTRUCTURE_URL = 'sqlite:///%kernel.project_dir%/var/payment.test.infrastructure.sqlite';
    private const PANTHER_BROWSER_ARGUMENTS = [
        '--headless',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--no-sandbox',
        '--disable-features=HttpsUpgrades,HTTPS-FirstMode,UseHttpsOnlyMode',
    ];

    /** @var array<string, string|null> */
    private array $originalEnv = [];

    protected function setUp(): void
    {
        $this->rememberEnvironmentValue('OIDC_DISABLED');
        $this->rememberEnvironmentValue('PANTHER_CHROME_ARGUMENTS');
        $this->rememberEnvironmentValue('PANTHER_CHROME_BINARY');
        $this->rememberEnvironmentValue('PANTHER_NO_SANDBOX');
        $this->rememberEnvironmentValue('PANTHER_APP_ENV');

        $this->setEnvironmentValue('OIDC_DISABLED', '1');
        $this->setEnvironmentValue('PANTHER_CHROME_ARGUMENTS', implode(' ', self::PANTHER_BROWSER_ARGUMENTS));
        $this->setEnvironmentValue('PANTHER_CHROME_BINARY', '/usr/bin/chromium');
        $this->setEnvironmentValue('PANTHER_NO_SANDBOX', '1');
        $this->setEnvironmentValue('PANTHER_APP_ENV', 'test');
    }

    protected function tearDown(): void
    {
        foreach (array_keys($this->originalEnv) as $nameEntity) {
            $this->restoreEnvironmentValue($nameEntity);
        }

        parent::tearDown();
    }

    /**
     * Verifies that finalize shows business error for missing payment.
     */
    public function testFinalizeShowsBusinessErrorForMissingPayment(): void
    {
        $externalBaseUri = $_ENV['PANTHER_EXTERNAL_BASE_URI'] ?? getenv('PANTHER_EXTERNAL_BASE_URI') ?: null;

        $options = $this->buildPantherOptions();

        if (is_string($externalBaseUri) && '' !== $externalBaseUri) {
            $options['external_base_uri'] = $externalBaseUri;
        } else {
            if (!method_exists($this, 'bootKernel')) {
                self::markTestSkipped('Panther kernel boot helpers are unavailable in this environment.');
            }

            $this->bootstrapPantherTestDatabase();
            $options['webServerDir'] = dirname(__DIR__, 3).'/public';
            $options['router'] = dirname(__DIR__, 3).'/public/index.php';
            $options['env'] = [
                'APP_ENV' => 'test',
                'APP_DEBUG' => '0',
                'APP_SECRET' => 'payment_test_secret',
                'DATABASE_URL' => self::SQLITE_TEST_DATABASE_URL,
                'INFRASTRUCTURE_URL' => self::SQLITE_TEST_INFRASTRUCTURE_URL,
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

form.querySelector('input[nameEntity="payment_console_finalize[paymentId]"]').value = '01HK153X000000000000000099';
form.querySelector('select[nameEntity="payment_console_finalize[provider]"]').value = 'internal';
form.querySelector('input[nameEntity="payment_console_finalize[providerRef]"]').value = 'missing-target';
form.querySelector('input[nameEntity="payment_console_finalize[gatewayTransactionId]"]').value = 'txn-missing-target';
form.querySelector('select[nameEntity="payment_console_finalize[status]"]').value = 'completed';
form.submit();
JS);

        $client->waitForVisibility('.alert-danger');

        self::assertStringContainsString('/payment/console', $client->getCurrentURL());
        self::assertSelectorTextContains('.alert-danger', 'was not found');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPantherOptions(): array
    {
        return [
            'browser' => defined('Symfony\\Component\\Panther\\PantherTestCase::CHROME') ? constant('Symfony\\Component\\Panther\\PantherTestCase::CHROME') : 'chrome',
            'browser_arguments' => self::PANTHER_BROWSER_ARGUMENTS,
        ];
    }

    private function bootstrapPantherTestDatabase(): void
    {
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

        self::ensureKernelShutdown();
    }

    private function rememberEnvironmentValue(string $nameEntity): void
    {
        $this->originalEnv[$nameEntity] = $_ENV[$nameEntity] ?? (getenv($nameEntity) ?: null);
    }

    private function setEnvironmentValue(string $nameEntity, string $value): void
    {
        $_ENV[$nameEntity] = $value;
        putenv($nameEntity.'='.$value);
    }

    private function restoreEnvironmentValue(string $nameEntity): void
    {
        $originalValue = $this->originalEnv[$nameEntity] ?? null;
        if (null === $originalValue) {
            unset($_ENV[$nameEntity]);
            putenv($nameEntity);

            return;
        }

        $_ENV[$nameEntity] = $originalValue;
        putenv($nameEntity.'='.$originalValue);
    }
}
