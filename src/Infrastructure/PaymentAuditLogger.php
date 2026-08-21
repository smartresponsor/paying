<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\Infrastructure\Entity\PaymentAuditEntity;
use App\Paying\InfrastructureInterface\PaymentAuditLoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persists payment audit records for operator and system-visible lifecycle actions.
 */
readonly class PaymentAuditLogger implements PaymentAuditLoggerInterface
{
    public function __construct(private EntityManagerInterface $data)
    {
    }

    /**
     * Writes a payment audit entry to the operational database.
     */
    public function log(string $action, array $context = []): void
    {
        $this->data->wrapInTransaction(function () use ($action, $context): void {
            $this->data->persist(new PaymentAuditEntity($action, $context));
            $this->data->flush();
        });
    }
}
