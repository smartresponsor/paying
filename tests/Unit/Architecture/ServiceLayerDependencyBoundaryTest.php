<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class ServiceLayerDependencyBoundaryTest extends TestCase
{
    public function testServiceAndServiceInterfaceDoNotDependOnControllerNamespace(): void
    {
        $serviceFiles = [
            ...(glob(__DIR__.'/../../../src/Service/*.php') ?: []),
            ...(glob(__DIR__.'/../../../src/ServiceInterface/*.php') ?: []),
        ];

        foreach ($serviceFiles as $file) {
            $content = (string) file_get_contents($file);

            self::assertStringNotContainsString(
                'App\\Controller\\',
                $content,
                sprintf('Service-layer file %s must not depend on controller namespace.', basename($file)),
            );
        }
    }
}
