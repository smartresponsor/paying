<?php

declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use PHPUnit\Framework\TestCase;

final class PaymentBundleExportConfigSmokeTest extends TestCase
{
    public function testBundleExportSurfaceFilesArePresent(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertFileExists($root.'/src/PayingBundle.php');
        self::assertFileExists($root.'/src/DependencyInjection/Configuration.php');
        self::assertFileExists($root.'/src/DependencyInjection/PayingExtension.php');
        self::assertFileExists($root.'/config/packages/paying.yaml');
        self::assertFileExists($root.'/docs/architecture/payment-bundle-export-surface.md');
        self::assertFileExists($root.'/config/services/payment_aliases.yaml');
    }

    public function testComposerAndConfigExposeDependencyOrientedSurface(): void
    {
        $root = dirname(__DIR__, 3);

        $composer = (string) file_get_contents($root.'/composer.json');
        $services = (string) file_get_contents($root.'/config/services.yaml');
        $framework = (string) file_get_contents($root.'/config/packages/payment_framework.yaml');
        $doctrine = (string) file_get_contents($root.'/config/packages/payment_doctrine.yaml');
        $messenger = (string) file_get_contents($root.'/config/packages/payment_messenger.yaml');

        self::assertStringContainsString('"type": "library"', $composer);
        self::assertStringNotContainsString('paying.app_secret', $services);
        self::assertStringContainsString('services/payment_aliases.yaml', $services);
        self::assertStringContainsString('paying.messenger.dsn', $services);
        self::assertStringContainsString('paying.storage.data_server_version', $services);
        self::assertStringContainsString("secret: '%env(APP_SECRET)%'", $framework);
        self::assertStringContainsString('paying.storage.data_server_version', $doctrine);
        self::assertStringContainsString('%paying.messenger.dsn%', $messenger);
        self::assertStringContainsString('%env(default::OIDC_ISSUER)%', $services);
        self::assertStringContainsString('%env(default::OIDC_AUDIENCE)%', $services);
    }
}
