<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Applies request-level payment rate limiting safeguards around HTTP entry points.
 */
readonly class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(private RateLimiterFactory $paymentApiLimiter)
    {
    }

    /**
     * Returns the Symfony event subscriptions exposed by this subscriber.
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onRequest'];
    }

    /**
     * Applies the configured API rate limit to payment HTTP requests.
     */
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
