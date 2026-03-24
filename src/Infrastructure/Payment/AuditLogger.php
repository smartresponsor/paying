<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

use Doctrine\DBAL\Connection;

class AuditLogger implements AuditLoggerInterface
{
    public function __construct(private readonly Connection $data)
    {
    }

    public function log(string $action, array $context = []): void
    {
        $this->data->insert('payment_audit', [
            'action' => $action,
            'context' => json_encode($context),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }
}
