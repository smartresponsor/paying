<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

/**
 * Defines the audit logging contract used by payment infrastructure services.
 */
interface AuditLoggerInterface
{
    /**
     * Writes a payment audit entry to the active audit sink.
     */
    public function log(string $action, array $context = []): void;
}
