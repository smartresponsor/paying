<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\RefundServiceInterface;
use App\Service\Payment\ProviderGuard;
use App\InfrastructureInterface\Payment\PaymentRepositoryInterface;
use Symfony\Component\Uid\Ulid;

class RefundService implements RefundServiceInterface
{
    public function __construct(
        private readonly ProviderGuard $guard,
        private readonly PaymentRepositoryInterface $repo
    ) {}

    public function refund(Ulid $id, string $amount, string $provider = 'internal')
    {
        $p = $this->guard->refund($provider, $id, $amount);
        $this->repo->save($p);
        return $p;
    }
}
