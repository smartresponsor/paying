<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\RepositoryInterface\PaymentOperationalRepositoryInterface;
use App\Paying\ServiceInterface\PaymentAuditLoggerInterface;

/**
 * Persists payment audit records for operator and system-visible lifecycle actions.
 */
readonly class PaymentAuditLogger implements PaymentAuditLoggerInterface
{
    public function __construct(private PaymentOperationalRepositoryInterface $operations)
    {
    }

    /**
     * Writes a payment audit entry to the operational database.
     */
    public function log(string $action, array $context = []): void
    {
        $this->operations->writeAudit($action, $context);
    }
}
