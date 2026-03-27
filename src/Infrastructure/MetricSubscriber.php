<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\Event\PaymentEvent;
use App\Service\Metric;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetricSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Metric $metrics)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'payment.success' => 'onSuccess',
            'payment.failure' => 'onFailure',
        ];
    }

    public function onSuccess(PaymentEvent $e): void
    {
        $this->metrics->incSuccess();
    }

    public function onFailure(PaymentEvent $e): void
    {
        $this->metrics->incFailure();
    }
}
