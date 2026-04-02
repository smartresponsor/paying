<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\ServiceInterface\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class TraceRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(private TracerInterface $tracer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $incoming = $request->headers->get('traceparent')
            ?? $request->headers->get('x-trace-id');

        $this->tracer->startTrace($incoming);
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $response->headers->set('x-trace-id', $this->tracer->currentTraceId());
        $response->headers->set('traceparent', $this->tracer->formatTraceparent());
    }
}
