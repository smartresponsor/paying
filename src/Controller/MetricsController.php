<?php

declare(strict_types=1);

namespace App\Controller;

use App\ServiceInterface\MetricInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final readonly class MetricsController
{
    public function __construct(private MetricInterface $metric)
    {
    }

    #[Route(path: '/metrics/payment', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response(
            $this->metric->export(),
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; version=0.0.4']
        );
    }
}
