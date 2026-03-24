<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use App\Event\Payment\PaymentEvent;
use App\Service\Payment\Metric;
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
