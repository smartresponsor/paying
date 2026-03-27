<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\ReconciliationServiceInterface;
use Symfony\Component\Uid\Ulid;

class ReconciliationService implements ReconciliationServiceInterface
{
    public function __construct(
        private readonly ProviderGuardInterface $guard,
        private readonly PaymentRepositoryInterface $repo,
    ) {
    }

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
