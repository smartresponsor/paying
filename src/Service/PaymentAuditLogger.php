<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\Operational\PaymentAuditEntity;
use App\Paying\ServiceInterface\PaymentAuditLoggerInterface;
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
