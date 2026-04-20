<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\InfrastructureInterface\AuditLoggerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Persists payment audit records for operator and system-visible lifecycle actions.
 */
readonly class AuditLogger implements AuditLoggerInterface
{
    public function __construct(private Connection $data)
    {
    }

    /**
     * Writes a payment audit entry to the operational database.
     *
     * @throws Exception
     */
    public function log(string $action, array $context = []): void
    {
        $this->data->insert('payment_audit', [
            'action' => $action,
            'context' => json_encode($context),
            'created_at' => new \DateTimeImmutable()->format('Y-m-d H:i:s'),
        ]);
    }
}
