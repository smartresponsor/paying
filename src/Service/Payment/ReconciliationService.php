<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\ReconciliationServiceInterface;
use App\Service\Payment\ProviderGuard;
use App\InfrastructureInterface\Payment\PaymentRepositoryInterface;
use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

class ReconciliationService implements ReconciliationServiceInterface
{
    public function __construct(
        private readonly ProviderGuard $guard,
        private readonly PaymentRepositoryInterface $repo
    ) {}

    public function reconcile(Ulid $id, string $provider = 'internal'): Payment
    {
        $p = $this->guard->reconcile($provider, $id);
        $this->repo->save($p);
        return $p;
    }

    public function listProcessingIds(int $limit = 100): array
    {
        return $this->repo->listIdsByStatuses(['processing'], $limit);
    }
}
