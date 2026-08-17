<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Exercises the controller validation boundary scenario within the payment architecture test surface.
 */
final class ControllerValidationBoundaryTest extends TestCase
{
    /**
     * Verifies that api controllers avoid direct validator and mapper imports.
     */
    public function testApiControllersAvoidDirectValidatorAndMapperImports(): void
    {
        $controllerFiles = glob(__DIR__.'/../../../src/Controller/*.php') ?: [];
        $forbiddenImports = [
            'use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;',
            'use App\Paying\\ServiceInterface\\PaymentValidationErrorMapperInterface;',
        ];

        foreach ($controllerFiles as $file) {
            $content = (string) file_get_contents($file);

            foreach ($forbiddenImports as $forbiddenImport) {
                self::assertStringNotContainsString(
                    $forbiddenImport,
                    $content,
                    sprintf('Controller %s should not import %s directly.', basename($file), $forbiddenImport),
                );
            }
        }
    }

    /**
     * Verifies that api controllers depend on shared request validator contract.
     */
    public function testApiControllersDependOnSharedRequestValidatorContract(): void
    {
        $apiControllers = [
            'PaymentFinalizeController.php',
            'PaymentCreateController.php',
            'PaymentRefundController.php',
            'PaymentStartController.php',
        ];

        foreach ($apiControllers as $controller) {
            $path = __DIR__.'/../../../src/Controller/'.$controller;
            $content = (string) file_get_contents($path);

            self::assertStringContainsString(
                'use App\Paying\\ServiceInterface\\PaymentApiRequestValidatorInterface;',
                $content,
                sprintf('Controller %s must use PaymentApiRequestValidatorInterface import.', $controller),
            );
            self::assertMatchesRegularExpression(
                '/private\s+(?:readonly\s+)?PaymentApiRequestValidatorInterface\s+\$requestValidator,/',
                $content,
                sprintf('Controller %s must inject PaymentApiRequestValidatorInterface.', $controller),
            );
        }
    }
}
