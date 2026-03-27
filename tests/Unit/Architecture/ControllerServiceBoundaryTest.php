<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class ControllerServiceBoundaryTest extends TestCase
{
    public function testControllersUseServiceInterfacesInsteadOfConcreteServices(): void
    {
        $controllerFiles = glob(__DIR__.'/../../../src/Controller/*.php') ?: [];
        $allowedConcreteUses = [
            'use App\\Service\\PaymentNotFoundException;',
        ];

        foreach ($controllerFiles as $file) {
            $content = (string) file_get_contents($file);
            preg_match_all('/^use App\\\\Service\\\\[^;]+;$/m', $content, $matches);

            foreach ($matches[0] as $import) {
                self::assertContains(
                    $import,
                    $allowedConcreteUses,
                    sprintf('Controller %s depends on concrete service import: %s', basename($file), $import),
                );
            }
        }
    }
}
