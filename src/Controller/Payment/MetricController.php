<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Attribute\Payment\RequireScope;
use App\Infrastructure\Payment\PaymentProjectionRepositoryInterface;
use App\Service\Payment\Metric;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

final class MetricController implements MetricControllerInterface
{
    public function __construct(
        private readonly Metric $metrics,
        private readonly Connection $data,
        private readonly PaymentProjectionRepositoryInterface $infra,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[RequireScope(['payment:read'])]
    public function metrics(): Response
    {
        $text = $this->metrics->export();

        try {
            $dataUpdatedAt = (string) ($this->data->fetchOne('SELECT MAX(updated_at) FROM payment') ?: '');
            $infraUpdatedAt = (string) ($this->infra->maxUpdatedAt() ?: '');
            $lagMs = 0;
            if ('' !== $dataUpdatedAt && '' !== $infraUpdatedAt) {
                $lagMs = max(0, (strtotime($dataUpdatedAt) - strtotime($infraUpdatedAt)) * 1000);
            }
            $text .= "payment_projection_lag_ms {$lagMs}\n";
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to calculate payment projection lag metrics.', ['exception' => $e]);
        }

        return new Response($text, Response::HTTP_OK, ['Content-Type' => 'text/plain; version=0.0.4']);
    }
}
