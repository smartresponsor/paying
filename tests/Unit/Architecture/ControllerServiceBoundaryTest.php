<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Exercises the controller service boundary scenario within the payment architecture test surface.
 */
final class ControllerServiceBoundaryTest extends TestCase
{
    /**
     * Verifies that controllers use service interfaces instead of concrete services.
     */
    public function testControllersUseServiceInterfacesInsteadOfConcreteServices(): void
    {
        $controllerFiles = glob(__DIR__.'/../../../src/Controller/*.php') ?: [];
        $allowedConcreteUses = [
            'use App\Paying\\Service\\PaymentNotFoundException;',
        ];

        foreach ($controllerFiles as $file) {
            $content = (string) file_get_contents($file);
            preg_match_all('/^use App\Paying\\\\Service\\\\[^;]+;$/m', $content, $matches);

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
