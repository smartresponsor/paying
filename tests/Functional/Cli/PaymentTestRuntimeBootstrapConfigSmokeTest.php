<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use PHPUnit\Framework\TestCase;

final class PaymentTestRuntimeBootstrapConfigSmokeTest extends TestCase
{
    public function testOwnedTestBootstrapScriptsAndConfigsArePresent(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $scripts = $composer['scripts'] ?? [];

        self::assertArrayHasKey('test:bootstrap', $scripts);
        self::assertArrayHasKey('test:bootstrap:reset', $scripts);
        self::assertArrayHasKey('test:bootstrap:migrate', $scripts);
        self::assertArrayHasKey('test:bootstrap:fixtures', $scripts);

        self::assertFileExists(dirname(__DIR__, 3).'/.env.test');
        self::assertFileExists(dirname(__DIR__, 3).'/config/packages/test/framework.yaml');
        self::assertFileExists(dirname(__DIR__, 3).'/config/packages/test/doctrine.yaml');
        self::assertFileExists(dirname(__DIR__, 3).'/config/packages/test/messenger.yaml');
        self::assertFileExists(dirname(__DIR__, 3).'/tools/runtime/payment_test_bootstrap.sh');
        self::assertFileExists(dirname(__DIR__, 3).'/tools/runtime/payment_test_bootstrap.ps1');
    }

    public function testTestDoctrineAndMessengerOverridesUseDeterministicLocalRuntime(): void
    {
        $doctrine = (string) file_get_contents(dirname(__DIR__, 3).'/config/packages/test/doctrine.yaml');
        $messenger = (string) file_get_contents(dirname(__DIR__, 3).'/config/packages/test/messenger.yaml');

        self::assertStringContainsString('payment.test.data.sqlite', $doctrine);
        self::assertStringContainsString('payment.test.infra.sqlite', $doctrine);
        self::assertStringContainsString("payment_outbox: 'in-memory://'", $messenger);
        self::assertStringContainsString("payment_outbox_failed: 'in-memory://'", $messenger);
        self::assertStringContainsString("payment_events_in: 'in-memory://'", $messenger);
    }
}
