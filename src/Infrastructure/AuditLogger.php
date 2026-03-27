<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\InfrastructureInterface\AuditLoggerInterface;
use Doctrine\DBAL\Connection;

readonly class AuditLogger implements AuditLoggerInterface
{
    public function __construct(private Connection $data)
    {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function log(string $action, array $context = []): void
    {
        $this->data->insert('payment_audit', [
            'action' => $action,
            'context' => json_encode($context),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }
}
