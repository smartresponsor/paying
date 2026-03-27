<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\ControllerInterface\MetricControllerInterface;
use App\ServiceInterface\MetricInterface;
use App\ServiceInterface\ProjectionLagServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

final class MetricController implements MetricControllerInterface
{
    public function __construct(
        private readonly MetricInterface $metrics,
        private readonly ProjectionLagServiceInterface $projectionLag,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[RequireScope(['payment:read'])]
    public function metrics(): Response
    {
        $text = $this->metrics->export();

        try {
            $snapshot = $this->projectionLag->snapshot();
            $text .= sprintf("payment_projection_lag_ms %d\n", $snapshot['projectionLagMs']);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to calculate payment projection lag metrics.', ['exception' => $e]);
        }

        return new Response($text, Response::HTTP_OK, ['Content-Type' => 'text/plain; version=0.0.4']);
    }
}
