<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Service\ApiJsonBodyDecoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exercises the api json body decoder scenario within the payment unit test surface.
 */
final class ApiJsonBodyDecoderTest extends TestCase
{
    /**
     * Verifies that decode returns array for valid json object.
     */
    public function testDecodeReturnsArrayForValidJsonObject(): void
    {
        $request = new Request([], [], [], [], [], [], '{"amount":"10.00"}');
        $decoder = new ApiJsonBodyDecoder();

        self::assertSame(['amount' => '10.00'], $decoder->decode($request));
    }

    /**
     * Verifies that decode returns null for invalid json.
     */
    public function testDecodeReturnsNullForInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], '{bad-json');
        $decoder = new ApiJsonBodyDecoder();

        self::assertNull($decoder->decode($request));
    }

    /**
     * Verifies that decode allows empty body when configured.
     */
    public function testDecodeAllowsEmptyBodyWhenConfigured(): void
    {
        $request = new Request([], [], [], [], [], [], '');
        $decoder = new ApiJsonBodyDecoder();

        self::assertSame([], $decoder->decode($request, true));
    }
}
