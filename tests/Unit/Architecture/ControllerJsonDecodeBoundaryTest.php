<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Exercises the controller json decode boundary scenario within the payment architecture test surface.
 */
final class ControllerJsonDecodeBoundaryTest extends TestCase
{
    /**
     * Verifies that top level controllers do not use raw json decode.
     */
    public function testTopLevelControllersDoNotUseRawJsonDecode(): void
    {
        $controllerFiles = glob(__DIR__.'/../../../src/Controller/*.php') ?: [];

        foreach ($controllerFiles as $file) {
            $content = (string) file_get_contents($file);

            self::assertStringNotContainsString(
                'json_decode(',
                $content,
                sprintf('Controller %s must use ApiJsonBodyDecoder instead of raw json_decode.', basename($file)),
            );
        }
    }
}
