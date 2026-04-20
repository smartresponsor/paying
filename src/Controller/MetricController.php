<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\Attribute\RequireScope;
use App\Paying\ControllerInterface\MetricControllerInterface;
use App\Paying\ServiceInterface\MetricInterface;
use App\Paying\ServiceInterface\ProjectionLagServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Publishes payment metrics and projection lag telemetry for Prometheus-style scraping.
 */
final readonly class MetricController implements MetricControllerInterface
{
    public function __construct(
        private MetricInterface $metrics,
        private ProjectionLagServiceInterface $projectionLag,
        private LoggerInterface $logger,
    ) {
    }

    #[RequireScope(['payment:read'])]
    #[OA\Get(
        path: '/metrics',
        summary: 'Render payment metrics and projection lag telemetry.',
        tags: ['Payment Operations'],
        responses: [
            new OA\Response(response: 200, description: 'Prometheus-style metrics payload.'),
            new OA\Response(response: 401, description: 'Missing or invalid bearer token.'),
            new OA\Response(response: 403, description: 'Missing payment:read scope.'),
        ],
    )]
    #[Security(name: 'Bearer')]
    /**
     * Renders the text-based metrics payload for observability collectors.
     */
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
