<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use PHPUnit\Framework\TestCase;

final class PaymentInstallRuntimePreflightConfigSmokeTest extends TestCase
{
    public function testInstallPreflightScriptsAndDocsArePresent(): void
    {
        $root = dirname(__DIR__, 3);
        $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $scripts = $composer['scripts'] ?? [];

        self::assertArrayHasKey('install:preflight', $scripts);
        self::assertArrayHasKey('install:proof:checklist', $scripts);
        self::assertArrayHasKey('runtime:preflight:sh', $scripts);
        self::assertArrayHasKey('runtime:preflight:ps1', $scripts);

        self::assertFileExists($root.'/tools/runtime/payment_install_preflight.sh');
        self::assertFileExists($root.'/tools/runtime/payment_install_preflight.ps1');
        self::assertFileExists($root.'/docs/architecture/payment-install-preflight-proof.md');
    }

    public function testInstallPreflightScriptsReferenceOwnedRuntimeFiles(): void
    {
        $root = dirname(__DIR__, 3);
        $shell = (string) file_get_contents($root.'/tools/runtime/payment_install_preflight.sh');
        $powershell = (string) file_get_contents($root.'/tools/runtime/payment_install_preflight.ps1');

        self::assertStringContainsString('.env.example', $shell);
        self::assertStringContainsString('.env.test', $shell);
        self::assertStringContainsString('phpunit.xml.dist', $shell);
        self::assertStringContainsString('composer.lock', $shell);

        self::assertStringContainsString('.env.example', $powershell);
        self::assertStringContainsString('.env.test', $powershell);
        self::assertStringContainsString('phpunit.xml.dist', $powershell);
        self::assertStringContainsString('composer.lock', $powershell);
    }
}
