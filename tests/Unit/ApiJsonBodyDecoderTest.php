<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\ApiJsonBodyDecoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ApiJsonBodyDecoderTest extends TestCase
{
    public function testDecodeReturnsArrayForValidJsonObject(): void
    {
        $request = new Request([], [], [], [], [], [], '{"amount":"10.00"}');
        $decoder = new ApiJsonBodyDecoder();

        self::assertSame(['amount' => '10.00'], $decoder->decode($request));
    }

    public function testDecodeReturnsNullForInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], '{bad-json');
        $decoder = new ApiJsonBodyDecoder();

        self::assertNull($decoder->decode($request));
    }

    public function testDecodeAllowsEmptyBodyWhenConfigured(): void
    {
        $request = new Request([], [], [], [], [], [], '');
        $decoder = new ApiJsonBodyDecoder();

        self::assertSame([], $decoder->decode($request, true));
    }
}
