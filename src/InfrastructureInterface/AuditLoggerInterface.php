<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface;

interface AuditLoggerInterface
{
    public function log(string $action, array $context = []): void;
}
