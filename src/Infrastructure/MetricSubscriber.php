<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\Event\PaymentEvent;
use App\Paying\Service\Metric;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Collects lightweight payment metrics from kernel and domain events.
 */
readonly class MetricSubscriber implements EventSubscriberInterface
{
    public function __construct(private Metric $metrics)
    {
    }

    /**
     * Returns the Symfony event subscriptions exposed by this subscriber.
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'payment.success' => 'onSuccess',
            'payment.failure' => 'onFailure',
        ];
    }

    /**
     * Records a successful payment event in the metrics collector.
     */
    public function onSuccess(PaymentEvent $e): void
    {
        $this->metrics->incSuccess();
    }

    /**
     * Records a failed payment event in the metrics collector.
     */
    public function onFailure(PaymentEvent $e): void
    {
        $this->metrics->incFailure();
    }
}
