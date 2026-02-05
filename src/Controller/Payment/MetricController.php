<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Controller\Payment;

use App\ControllerInterface\Payment\MetricControllerInterface;
use App\Service\Payment\Metric;
use Doctrine\DBAL\Connection;
use App\InfrastructureInterface\Payment\PaymentProjectionRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MetricController implements MetricControllerInterface
{
    public function __construct(private readonly Metric $metrics, private readonly Connection $data, private readonly PaymentProjectionRepositoryInterface $infra) {}

    #[Route(path: '/metrics', name: 'metrics', methods: ['GET'])]
    public function metrics(): Response
    {
        $text = $this->metrics->export();
        try {
            $d = (string)($this->data->fetchOne('SELECT MAX(updated_at) FROM payment') ?: '');
            $i = (string)($this->infra->maxUpdatedAt() ?: '');
            $lagMs = 0;
            if ($d && $i) { $lagMs = max(0, (strtotime($d) - strtotime($i)) * 1000); }
            $text .= "payment_projection_lag_ms {$lagMs}\n";
        } catch (\Throwable $e) {}
        return new Response($text, 200, ['Content-Type' => 'text/plain; version=0.0.4']);
    }
}
