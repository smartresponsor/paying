<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Infrastructure\Payment;

interface AuditLoggerInterface
{
    public function log(string $action, array $context = []): void;
}
