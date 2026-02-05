<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\InfrastructureInterface\Payment;

interface AuditLoggerInterface
{
    public function log(string $action, array $context = []): void;
}
