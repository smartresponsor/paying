<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly RateLimiterFactory $paymentApiLimiter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onRequest'];
    }

    public function onRequest(RequestEvent $event): void
    {
        $req = $event->getRequest();
        if (!str_starts_with($req->getPathInfo(), '/payment/')) {
            return;
        }
        $limit = $this->paymentApiLimiter->create($req->getClientIp() ?? 'anon');
        $res = $limit->consume();
        if (!$res->isAccepted()) {
            $event->setResponse(new Response('', 429));
        }
    }
}
