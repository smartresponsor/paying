<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Controller\Payment;

use App\ControllerInterface\Payment\MetricsControllerInterface;
use App\Service\Payment\Metrics;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MetricsController implements MetricsControllerInterface
{
    public function __construct(private readonly Metrics $metrics) {}

    #[Route(path: '/metrics', name: 'metrics', methods: ['GET'])]
    public function metrics(): Response
    {
        return new Response($this->metrics->export(), 200, ['Content-Type' => 'text/plain; version=0.0.4']);
    }
}
