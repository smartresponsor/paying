<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Infrastructure\ResponseHeaderSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Exercises the response header subscriber within the payment unit test surface.
 */
final class ResponseHeaderSubscriberTest extends TestCase
{
    public function testOnResponseAddsHeadersToMainRequest(): void
    {
        $subscriber = new ResponseHeaderSubscriber("default-src 'self'; frame-ancestors 'none'");
        $response = new Response();
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $subscriber->onResponse($event);

        self::assertSame("default-src 'self'; frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame('DENY', $response->headers->get('X-Frame-Options'));
        self::assertSame('no-referrer', $response->headers->get('Referrer-Policy'));
    }

    public function testOnResponseIgnoresSubRequest(): void
    {
        $subscriber = new ResponseHeaderSubscriber("default-src 'self'");
        $response = new Response();
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $response,
        );

        $subscriber->onResponse($event);

        self::assertFalse($response->headers->has('Content-Security-Policy'));
    }
}
