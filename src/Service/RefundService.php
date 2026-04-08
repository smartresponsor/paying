<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the refund service service used by the payment lifecycle and operator-facing flows.
 */
final readonly class RefundService implements RefundServiceInterface
{
    public function __construct(
        private ProviderGuardInterface $guard,
        private PaymentRepositoryInterface $repo,
    ) {
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(Ulid $id, string $amount, string $provider = 'internal'): Payment
    {
        $existing = $this->repo->find((string) $id);
        if (null === $existing) {
            throw PaymentNotFoundException::byId((string) $id);
        }

        $resolved = $this->guard->refund($provider, $id, $amount);
        $existing->syncFrom($resolved);
        $this->repo->save($existing);

        return $existing;
    }
}
