<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use PHPUnit\Framework\TestCase;

final class PaymentFixtureBootstrapConfigSmokeTest extends TestCase
{
    public function testComposerOwnsPaymentFixtureEntryPoints(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $scripts = $composer['scripts'] ?? [];

        self::assertArrayHasKey('fixtures:payment:load', $scripts);
        self::assertArrayHasKey('fixtures:payment:append', $scripts);
        self::assertSame('@php tools/php/php84.php bin/console doctrine:fixtures:load --group=payment --no-interaction', $scripts['fixtures:payment:load']);
        self::assertSame('@php tools/php/php84.php bin/console doctrine:fixtures:load --group=payment --append --no-interaction', $scripts['fixtures:payment:append']);
    }

    public function testPhpUnitBootstrapAndReadmeMentionFixtureContour(): void
    {
        $root = dirname(__DIR__, 3);
        $phpUnitXml = (string) file_get_contents($root.'/phpunit.xml.dist');
        $readme = (string) file_get_contents($root.'/README.md');

        self::assertStringContainsString('bootstrap="tests/bootstrap.php"', $phpUnitXml);
        self::assertStringContainsString('docs/OPERATIONS.md', $readme);
        self::assertStringContainsString('payment:outbox:process', $readme);
        self::assertStringContainsString('/payment/console', $readme);
    }
}
