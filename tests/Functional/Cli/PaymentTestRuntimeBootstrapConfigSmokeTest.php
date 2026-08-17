<?php

declare(strict_types=1);

namespace App\Paying\Tests\Functional\Cli;

use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversNothing]
final class PaymentTestRuntimeBootstrapConfigSmokeTest extends KernelTestCase
{
    public function testOwnedTestBootstrapScriptsAndConfigsArePresent(): void
    {
        self::assertFileExists(dirname(__DIR__, 3).'/tests/bootstrap.php');
        self::assertFileExists(dirname(__DIR__, 3).'/config/packages/test/payment_doctrine.yaml');
    }

    public function testTestDoctrineAndMessengerOverridesUseDeterministicLocalRuntime(): void
    {
        $config = (string) file_get_contents(dirname(__DIR__, 3).'/config/packages/test/payment_doctrine.yaml');

        self::assertTrue(
            str_contains($config, 'driver: pdo_sqlite') || str_contains($config, "url: '%env(resolve:DATABASE_URL)%'"),
            'Expected deterministic test DB override via sqlite driver/path or explicit DATABASE_URL.'
        );

        self::assertStringContainsString('payment.test.data.sqlite', $config);
        self::assertStringContainsString('payment.test.infrastructure.sqlite', $config);
    }
}
