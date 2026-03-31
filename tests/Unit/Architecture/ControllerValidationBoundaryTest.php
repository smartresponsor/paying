<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class ControllerValidationBoundaryTest extends TestCase
{
    public function testApiControllersAvoidDirectValidatorAndMapperImports(): void
    {
        $controllerFiles = glob(__DIR__.'/../../../src/Controller/*.php') ?: [];
        $forbiddenImports = [
            'use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;',
            'use App\\ServiceInterface\\ValidationErrorMapperInterface;',
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

    public function testApiControllersDependOnSharedRequestValidatorContract(): void
    {
        $apiControllers = [
            'FinalizeController.php',
            'PaymentCreateController.php',
            'PaymentRefundController.php',
            'StartController.php',
        ];

        foreach ($apiControllers as $controller) {
            $path = __DIR__.'/../../../src/Controller/'.$controller;
            $content = (string) file_get_contents($path);

            self::assertStringContainsString(
                'use App\\ServiceInterface\\ApiRequestValidatorInterface;',
                $content,
                sprintf('Controller %s must use ApiRequestValidatorInterface import.', $controller),
            );
            self::assertMatchesRegularExpression(
                '/private\s+(?:readonly\s+)?ApiRequestValidatorInterface\s+\$requestValidator,/',
                $content,
                sprintf('Controller %s must inject ApiRequestValidatorInterface.', $controller),
            );
        }
    }
}
