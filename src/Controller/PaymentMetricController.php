<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\ControllerInterface\PaymentMetricControllerInterface;
use App\Paying\ServiceInterface\PaymentMetricInterface;
use App\Paying\ServiceInterface\PaymentProjectionLagServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Publishes payment metrics and projection lag telemetry for Prometheus-style scraping.
 */
final readonly class PaymentMetricController implements PaymentMetricControllerInterface
{
    public function __construct(
        private PaymentMetricInterface $metrics,
        private PaymentProjectionLagServiceInterface $projectionLag,
        private LoggerInterface $logger,
    ) {
    }

    #[PaymentRequireScopeAttribute(['payment:read'])]
    #[OA\Get(
        path: '/metrics',
        summary: 'Render payment metrics and projection lag telemetry.',
        tags: ['PaymentEntity Operations'],
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
