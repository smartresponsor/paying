<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use App\Entity\Payment;
use App\Repository\PaymentRepositoryInterface;
use Symfony\Component\Uid\Ulid;

class RefundService implements RefundServiceInterface
{
    public function __construct(
        private readonly ProviderGuardInterface $guard,
        private readonly PaymentRepositoryInterface $repo,
    ) {
    }

    public function refund(Ulid $id, string $amount, string $provider = 'internal'): Payment
    {
        $existing = $this->repo->find((string) $id);
        if (null === $existing) {
            throw new \RuntimeException('Payment not found: '.(string) $id);
        }

        $resolved = $this->guard->refund($provider, $id, $amount);
        $existing->syncFrom($resolved);
        $this->repo->save($existing);

        return $existing;
    }
}
