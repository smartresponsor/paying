<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentEntity;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentProviderGuardInterface;
use App\Paying\ServiceInterface\PaymentReconciliationServiceInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the reconciliation service service used by the payment lifecycle and operator-facing flows.
 */
readonly class PaymentReconciliationService implements PaymentReconciliationServiceInterface
{
    public function __construct(
        private PaymentProviderGuardInterface $guard,
        private PaymentRepositoryInterface $repo,
    ) {
    }

    /**
     * Provides the reconcile behavior for the reconciliation service component.
     */
    public function reconcile(Ulid $id, string $provider = 'internal'): PaymentEntity
    {
        $p = $this->guard->reconcile($provider, $id);
        $this->repo->save($p);

        return $p;
    }

    /**
     * Returns the collection assembled by the list processing ids query path.
     */
    public function listProcessingIds(int $limit = 100): array
    {
        return $this->repo->listIdsByStatuses(['processing'], $limit);
    }
}
