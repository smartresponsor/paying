<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Controller\Payment;

use App\ControllerInterface\Payment\StatusControllerInterface;
use App\InfrastructureInterface\Payment\PaymentProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class StatusController implements StatusControllerInterface
{
    public function __construct(private readonly Connection $data, private readonly PaymentProjectionRepositoryInterface $infra) {}

    #[Route(path: '/status', name: 'status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        $d = (string)($this->data->fetchOne('SELECT MAX(updated_at) FROM payment') ?: '');
        $i = (string)($this->infra->maxUpdatedAt() ?: '');
        $lagMs = 0;
        try {
            $lagMs = max(0, (strtotime($d) - strtotime($i)) * 1000);
        } catch (\Throwable $e) {}
        return new JsonResponse(['updatedAtData'=>$d, 'updatedAtInfra'=>$i, 'projectionLagMs'=>$lagMs], 200);
    }
}
