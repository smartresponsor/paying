<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Entity\PaymentEntity;
use Symfony\Component\Uid\Ulid;

/**
 * Defines the contract for the refund service interface payment service boundary.
 */
interface RefundServiceInterface
{
    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(Ulid $id, string $amount, string $provider = 'internal'): PaymentEntity;
}
