<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds payment-related response headers for observability and client diagnostics.
 */
readonly class PaymentResponseHeaderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $contentSecurityPolicy = "default-src 'self'",
    ) {
    }

    /**
     * Returns the Symfony event subscriptions exposed by this subscriber.
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onResponse'];
    }

    /**
     * Applies the standard payment HTTP response headers to the outgoing response.
     */
    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');
    }
}
