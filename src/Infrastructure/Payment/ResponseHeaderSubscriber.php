<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseHeaderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onResponse'];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $resp = $event->getResponse();
        $csp = (string) ($_ENV['CSP_HEADER'] ?? "default-src 'self'");
        $resp->headers->set('Content-Security-Policy', $csp);
        $resp->headers->set('X-Content-Type-Options', 'nosniff');
        $resp->headers->set('X-Frame-Options', 'DENY');
        $resp->headers->set('Referrer-Policy', 'no-referrer');
    }
}
