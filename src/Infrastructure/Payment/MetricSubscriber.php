<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Service\Payment\Metric;
use App\Domain\Payment\Event\PaymentEvent;

class MetricSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Metric $metrics) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'payment.success' => 'onSuccess',
            'payment.failure' => 'onFailure',
        ];
    }

    public function onSuccess(PaymentEvent $e): void { $this->metrics->incSuccess(); }
    public function onFailure(PaymentEvent $e): void { $this->metrics->incFailure(); }
}
